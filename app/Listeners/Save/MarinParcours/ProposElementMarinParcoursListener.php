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

class ProposElementMarinParcoursListener
{
    public $comment;

    /**
     * Gère l'événement de proposition.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS)) {
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
            'PROPOSEUR INCONNU';

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

        $this->updateParcoursMarinItems(
            $parcoursMarin,
            $data['type_element'],
            $data['uuid_element'],
            $valideurName
        );

        // RECALCULER LES ÉTATS DE VALIDATION HIÉRARCHIQUES
        ReCalculEtatParcoursService::recalculerEtats($parcoursMarin);

        // Recalcul des taux
        $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
        $donnees_du_parcours->taux_global   = $parcoursMarin['taux_global']   ?? 0;
        $donnees_du_parcours->taux_stage    = $parcoursMarin['taux_stage']    ?? 0;
        $donnees_du_parcours->taux_activite = $parcoursMarin['taux_activite'] ?? 0;

        // Sauvegarder les données
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification de succès
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::PROPOS_ELMT_MARIN_PARCOURS,
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
     * ✅ METTRE À JOUR LES ÉLÉMENTS POUR PROPOSITION (nouvelle structure)
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type_element, $uuid_element, $displayName)
    {
        Log::info("=== PROPOSITION ===");
        Log::info("Type: {$type_element} / UUID: {$uuid_element}");
        $found = false;

        foreach ($parcoursMarin['annees'] as &$annee) {
            // --- TRAITEMENT DIRECT DES ANNÉES ---
            if ($type_element === 'annee' && $annee['uuid'] == $uuid_element) {
                $this->updateElement($annee, $displayName);
                Log::info("✅ Année proposée: {$annee['uuid']}");
                $found = true;
                return;
            }

            // --- TRAITEMENT DES SAVOIR-FAIRES ---
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    if ($type_element === 'savoirfaire') {
                        $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
                        
                        if ($savoirFaire['uuid'] == $uuid_element || $pivotUuid == $uuid_element) {
                            $this->updateElement($savoirFaire, $displayName);
                            Log::info("✅ Savoir-faire proposé: {$savoirFaire['uuid']}");
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
                                    $this->updateElement($objectif, $displayName);
                                    Log::info("✅ Objectif proposé: {$objUuidSimple} / {$objUuidComposite}");
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
            Log::error("❌ ÉLÉMENT NON TROUVÉ POUR PROPOSITION: {$type_element} / {$uuid_element}");
        }
    }

    /**
     * ✅ MÉTHODE HELPER POUR PROPOSER UN ÉLÉMENT
     */
    protected function updateElement(&$element, $displayName)
    {
        $element['commentaire'] = $this->comment;
        $element['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
        
        Log::info("Élément proposé avec succès");
    }
}
