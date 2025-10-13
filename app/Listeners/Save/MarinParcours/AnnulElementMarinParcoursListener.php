<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;
use App\Models\User;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\Services\CalculTauxParcoursService;
use Modules\FcmCommun\Services\ReCalculEtatParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\Log;

class AnnulElementMarinParcoursListener
{
    public $comment;

    /**
     * Gère l'événement d'annulation.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::where('uuid', $event->object_uuid)->first();
       
        if (!$marin) {
            Log::error('Marin non trouvé avec UUID: ' . $event->object_uuid);
            return;
        }

        $data = $event->detail;
        $this->comment = ''; // Pour une dévalidation, on remet le commentaire à vide

        $valideur = User::where('uuid', $event->user_uuid)->first();
        $valideurName = $valideur ?
            ($valideur->display_name ?? $valideur->nom . ' ' . $valideur->prenom) :
            'ANNULEUR INCONNU';

        $donnees_du_parcours = $marin->complements_fcm->donnees_du_parcours;
       
        if (!$donnees_du_parcours) {
            Log::error('Données du parcours non trouvées pour le marin: ' . $marin->uuid);
            return;
        }

        $parcoursMarin = $donnees_du_parcours->parcoursmarin;
       
        if (!$parcoursMarin) {
            Log::error('Parcours marin non trouvé ou vide pour le marin: ' . $marin->uuid);
            return;
        }

        // ✅ GESTION SPÉCIALE POUR LES SAVOIR-FAIRES AVEC ANNULATION EN CASCADE
        if ($data['type_element'] === 'savoirfaire') {
            $this->annulSavoirfaireParcoursMarinItems(
                $parcoursMarin,
                $data['uuid_element'],
                $valideurName
            );
        } else {
            // ✅ GESTION CLASSIQUE POUR LES AUTRES ÉLÉMENTS
            $this->updateParcoursMarinItems(
                $parcoursMarin,
                $data['type_element'],
                $data['uuid_element'],
                $valideurName
            );
        }

        // RECALCULER LES ÉTATS DE VALIDATION HIÉRARCHIQUES
        ReCalculEtatParcoursService::recalculerEtats($parcoursMarin);

        // Recalcul des taux
        $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
        $donnees_du_parcours->taux_global = $parcoursMarin['taux_global'] ?? 0;
        $donnees_du_parcours->taux_stage = $parcoursMarin['taux_stage'] ?? 0;
        $donnees_du_parcours->taux_activite = $parcoursMarin['taux_activite'] ?? 0;

        // Sauvegarder les données
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification de succès
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::ANNUL_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->warning() // ✅ WARNING au lieu de SUCCESS pour l'annulation
            ->body($notification['message'])
            ->send();
    }

    /**
     * ✅ MÉTHODE POUR ANNULER SAVOIR-FAIRE ET SES OBJECTIFS EN CASCADE
     */
    protected function annulSavoirfaireParcoursMarinItems(&$parcoursMarin, $savoirfaire_uuid, $displayName)
    {
        $objectifs_annules = 0;
        $found = false;

        Log::info("=== ANNULATION SAVOIR-FAIRE ===");
        Log::info("Recherche du savoir-faire UUID: {$savoirfaire_uuid}");

        foreach ($parcoursMarin['annees'] as &$annee) {
            if (!isset($annee['savoirfaires'])) {
                continue;
            }

            foreach ($annee['savoirfaires'] as &$savoirFaire) {
                // ✅ CONSTRUIRE L'UUID COMPOSITE COMME DANS L'INTERFACE
                $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
               
                Log::info("Comparaison: interface='{$savoirfaire_uuid}' vs parcours='{$pivotUuid}'");
               
                if ($savoirFaire['uuid'] === $savoirfaire_uuid || $pivotUuid === $savoirfaire_uuid) {
                    $found = true;
                   
                    Log::info("✅ SAVOIR-FAIRE TROUVÉ POUR ANNULATION!");

                    // ✅ ANNULER LE SAVOIR-FAIRE
                    $this->annulElement($savoirFaire, $displayName);
                    Log::info("✅ Savoir-faire annulé: {$savoirFaire['uuid']}");

                    // ✅ ANNULER TOUS SES OBJECTIFS VALIDÉS
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        Log::info("Annulation automatique de " . count($savoirFaire['objectifs']) . " objectifs");
                       
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            // Seulement si validé
                            if (isset($objectif['etat_valid']) && $objectif['etat_valid']) {
                                $this->annulElement($objectif, $displayName);
                                $objectifs_annules++;
                               
                                $objUuid = $objectif['uuid'] ?? "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                                Log::info("  ✅ Objectif auto-annulé: {$objUuid}");
                            }
                        }
                    }

                    Log::info("✅ RÉSULTAT: Savoir-faire {$savoirfaire_uuid} annulé avec {$objectifs_annules} objectifs auto-annulés");
                    return;
                }
            }
        }

        if (!$found) {
            Log::error("❌ SAVOIR-FAIRE NON TROUVÉ POUR ANNULATION: {$savoirfaire_uuid}");
        }
    }

    /**
     * ✅ METTRE À JOUR LES ÉLÉMENTS POUR ANNULATION (nouvelle structure)
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type_element, $uuid_element, $displayName)
    {
        Log::info("=== ANNULATION INDIVIDUELLE ===");
        Log::info("Type: {$type_element} / UUID: {$uuid_element}");
        $found = false;

        foreach ($parcoursMarin['annees'] as &$annee) {
            // --- TRAITEMENT DIRECT DES ANNÉES ---
            if ($type_element === 'annee' && $annee['uuid'] == $uuid_element) {
                $this->annulElement($annee, $displayName);
                Log::info("✅ Année annulée: {$annee['uuid']}");
                $found = true;
                return;
            }

            // --- TRAITEMENT DES SAVOIR-FAIRES ---
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    if ($type_element === 'savoirfaire') {
                        $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
                       
                        if ($savoirFaire['uuid'] == $uuid_element || $pivotUuid == $uuid_element) {
                            $this->annulElement($savoirFaire, $displayName);
                            Log::info("✅ Savoir-faire annulé: {$savoirFaire['uuid']}");
                            $found = true;
                            return;
                        }
                    }

                    // --- TRAITEMENT DES OBJECTIFS ---
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            if ($type_element === 'objectif') {
                                $objUuidComposite = "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                                $objUuidSimple = $objectif['uuid'] ?? '';
                               
                                if ($objUuidSimple == $uuid_element || $objUuidComposite == $uuid_element) {
                                    $this->annulElement($objectif, $displayName);
                                    Log::info("✅ Objectif annulé: {$objUuidSimple} / {$objUuidComposite}");
                                    $found = true;
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$found) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ POUR ANNULATION: {$type_element} / {$uuid_element}");
        }
    }

    /**
     * ✅ MÉTHODE HELPER POUR ANNULER UN ÉLÉMENT
     */
    protected function annulElement(&$element, $displayName)
    {
        $element['etat_valid'] = false; // ✅ DIFFÉRENCE PRINCIPALE : false au lieu de true
        $element['date_proposition'] = null;
        $element['commentaire'] = $this->comment;
        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
       
        Log::info("Élément annulé avec succès");
    }
}
