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

class ValidElementMarinParcoursListener
{
    public $comment;
    public $selectedItems;

    /**
     * Gère l'événement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::where('uuid', $event->object_uuid)->first();

        if (!$marin) {
            Log::error('Marin non trouvé avec UUID: ' . $event->object_uuid);
            return;
        }

        $data = $event->detail;
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');

        $valideur = User::where('uuid', $event->user_uuid)->first();
        $valideurName = $valideur ?
            ($valideur->display_name ?? $valideur->nom . ' ' . $valideur->prenom) :
            'VALIDEUR INCONNU';

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

        // ✅ GESTION SPÉCIALE POUR LES SAVOIR-FAIRES AVEC AUTO-VALIDATION
        if ($data['type_element'] === 'savoirfaire') {
            $this->updateSavoirfaireParcoursMarinItems(
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

        // ✅ RECALCULER LES ÉTATS DE VALIDATION HIÉRARCHIQUES
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
            UserGeneratedEventNotifications::VALID_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }

    /**
     * ✅ MÉTHODE POUR NETTOYER LES COMMENTAIRES
     */
    protected function sanitizeComment(string $comment): string
    {
        if (empty($comment)) {
            return '';
        }

        $comment = strip_tags($comment);
        $comment = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $comment);
        $comment = preg_replace('/\s+/', ' ', $comment);
        $comment = addslashes(trim($comment));

        return $comment;
    }

    /**
     * ✅ MÉTHODE CORRIGÉE POUR VALIDER SAVOIR-FAIRES ET SES OBJECTIFS
     */
    protected function updateSavoirfaireParcoursMarinItems(&$parcoursMarin, $savoirfaire_uuid, $displayName)
    {
        $objectifs_validees = 0;
        $found = false;

        Log::info("=== VALIDATION SAVOIR-FAIRE ===");
        Log::info("Recherche du savoir-faire UUID: {$savoirfaire_uuid}");

        foreach ($parcoursMarin['annees'] as &$annee) {
            if (!isset($annee['savoirfaires'])) {
                continue;
            }

            foreach ($annee['savoirfaires'] as &$savoirFaire) {
                // ✅ CONSTRUIRE L'UUID COMPOSITE COMME DANS L'INTERFACE
                $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
                
                Log::info("Comparaison: interface='{$savoirfaire_uuid}' vs parcours='{$pivotUuid}'");
                
                // ✅ VÉRIFIER BOTH FORMATS: UUID simple ET UUID composite
                if ($savoirFaire['uuid'] === $savoirfaire_uuid || $pivotUuid === $savoirfaire_uuid) {
                    $found = true;
                    
                    Log::info("✅ SAVOIR-FAIRE TROUVÉ!");
                    Log::info("  - UUID SF: {$savoirFaire['uuid']}");
                    Log::info("  - Pivot ID: {$savoirFaire['pivot']['id']}");
                    Log::info("  - UUID composite: {$pivotUuid}");

                    // ✅ VALIDER LE SAVOIR-FAIRE
                    $this->updateElement($savoirFaire, $displayName);
                    Log::info("✅ Savoir-faire validé: {$savoirFaire['uuid']}");

                    // ✅ VALIDER TOUS SES OBJECTIFS NON VALIDÉS
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        Log::info("Validation automatique de " . count($savoirFaire['objectifs']) . " objectifs");
                        
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            // Seulement si pas déjà validé
                            if (!isset($objectif['etat_valid']) || !$objectif['etat_valid']) {
                                $this->updateElement($objectif, $displayName);
                                $objectifs_validees++;
                                
                                $objUuid = $objectif['uuid'] ?? "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                                Log::info("  ✅ Objectif auto-validé: {$objUuid}");
                            } else {
                                $objUuid = $objectif['uuid'] ?? "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                                Log::info("  ⏭️  Objectif déjà validé: {$objUuid}");
                            }
                        }
                    } else {
                        Log::info("Aucun objectif à valider pour ce savoir-faire");
                    }

                    Log::info("✅ RÉSULTAT: Savoir-faire {$savoirfaire_uuid} validé avec {$objectifs_validees} objectifs auto-validés");
                    return; // ✅ Sortir une fois trouvé
                }
            }
        }

        if (!$found) {
            Log::error("❌ SAVOIR-FAIRE NON TROUVÉ: {$savoirfaire_uuid}");
            Log::error("UUIDs disponibles dans le parcours:");
            
            // ✅ DEBUG: Lister tous les UUIDs disponibles
            foreach ($parcoursMarin['annees'] as $annee) {
                if (isset($annee['savoirfaires'])) {
                    foreach ($annee['savoirfaires'] as $sf) {
                        $pivotUuid = $sf['uuid'] . '_' . ($sf['pivot']['uuid'] ?? $sf['pivot']['id'] ?? '');
                        Log::error("  - SF UUID simple: {$sf['uuid']}");
                        Log::error("  - SF UUID composite: {$pivotUuid}");
                    }
                }
            }
        }
    }

    /**
     * ✅ METTRE À JOUR LES ÉLÉMENTS (pour les autres types) - CORRIGÉ
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type_element, $uuid_element, $displayName)
    {
        Log::info("=== VALIDATION INDIVIDUELLE ===");
        Log::info("Type: {$type_element} / UUID: {$uuid_element}");
        $found = false;

        foreach ($parcoursMarin['annees'] as &$annee) {
            // --- TRAITEMENT DIRECT DES ANNÉES ---
            if ($type_element === 'annee' && $annee['uuid'] == $uuid_element) {
                $this->updateElement($annee, $displayName);
                Log::info("✅ Année mise à jour: {$annee['uuid']}");
                $found = true;
                return;
            }

            // --- TRAITEMENT DES SAVOIR-FAIRES ---
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    // ✅ POUR LES SAVOIR-FAIRES, VÉRIFIER LES DEUX FORMATS D'UUID
                    if ($type_element === 'savoirfaire') {
                        $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
                        
                        if ($savoirFaire['uuid'] == $uuid_element || $pivotUuid == $uuid_element) {
                            $this->updateElement($savoirFaire, $displayName);
                            Log::info("✅ Savoir-faire mis à jour: {$savoirFaire['uuid']} (recherché: {$uuid_element})");
                            $found = true;
                            return;
                        }
                    }

                    // --- TRAITEMENT DES OBJECTIFS ---
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            if ($type_element === 'objectif') {
                                // ✅ CONSTRUIRE L'UUID OBJECTIF COMME DANS L'INTERFACE
                                $objUuidComposite = "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                                $objUuidSimple = $objectif['uuid'] ?? '';
                                
                                if ($objUuidSimple == $uuid_element || $objUuidComposite == $uuid_element) {
                                    $this->updateElement($objectif, $displayName);
                                    Log::info("✅ Objectif mis à jour: {$objUuidSimple} / {$objUuidComposite} (recherché: {$uuid_element})");
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
            Log::error("❌ ÉLÉMENT NON TROUVÉ: {$type_element} / {$uuid_element}");
            
            // ✅ DEBUG: Lister les UUIDs disponibles selon le type
            if ($type_element === 'savoirfaire') {
                Log::error("UUIDs savoir-faires disponibles:");
                foreach ($parcoursMarin['annees'] as $annee) {
                    if (isset($annee['savoirfaires'])) {
                        foreach ($annee['savoirfaires'] as $sf) {
                            $pivotUuid = $sf['uuid'] . '_' . ($sf['pivot']['uuid'] ?? $sf['pivot']['id'] ?? '');
                            Log::error("  - {$sf['uuid']} / {$pivotUuid}");
                        }
                    }
                }
            } elseif ($type_element === 'objectif') {
                Log::error("UUIDs objectifs disponibles:");
                foreach ($parcoursMarin['annees'] as $annee) {
                    if (isset($annee['savoirfaires'])) {
                        foreach ($annee['savoirfaires'] as $sf) {
                            if (isset($sf['objectifs'])) {
                                foreach ($sf['objectifs'] as $obj) {
                                    $objUuid = "obj_{$sf['pivot']['id']}_{$obj['id']}";
                                    Log::error("  - {$objUuid}");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * ✅ MÉTHODE HELPER POUR METTRE À JOUR UN ÉLÉMENT
     */
    protected function updateElement(&$element, $displayName)
    {
        $element['etat_valid'] = true;
        $element['commentaire'] = $this->comment;
        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
        $element["date_proposition"] = null; // On retire la mention d'une éventuelle proposition antérieure.
        
        Log::info("Élément mis à jour avec succès");
    }
}
