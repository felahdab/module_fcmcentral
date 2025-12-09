<?php
namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;
use App\Models\User;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\Log;

class AnnulProposElementMarinParcoursListener
{
    public $comment;

    /**
     * Gère l'événement d'annulation de proposition.
     * Efface uniquement la proposition, ne touche pas à la validation ni au parcours.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(\Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS)) {
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

        $this->effacerProposition($parcoursMarin, $data['type_element'], $data['uuid_element']);

        // Sauvegarder les données (pas de recalcul, pas de taux modifié)
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification de succès
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->warning()
            ->body($notification['message'])
            ->send();
    }

    /**
     * Efface la proposition sur l'élément ciblé (ne touche pas à la validation).
     */
    protected function effacerProposition(&$parcoursMarin, $type_element, $uuid_element)
    {
        Log::info("=== ANNULATION DE PROPOSITION (effacement) ===");
        $found = false;

        foreach ($parcoursMarin['annees'] as &$annee) {
            // Année
            if ($type_element === 'annee' && $annee['uuid'] == $uuid_element) {
                $this->clearProposition($annee);
                Log::info("✅ Proposition d'année effacée: {$annee['uuid']}");
                $found = true;
                return;
            }

            // Savoir-faire
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    if ($type_element === 'savoirfaire') {
                        $pivotUuid = $savoirFaire['uuid'] . '_' . ($savoirFaire['pivot']['uuid'] ?? $savoirFaire['pivot']['id'] ?? '');
                        if ($savoirFaire['uuid'] == $uuid_element || $pivotUuid == $uuid_element) {
                            $this->clearProposition($savoirFaire);
                            Log::info("✅ Proposition de savoir-faire effacée: {$savoirFaire['uuid']}");
                            $found = true;
                            return;
                        }
                    }

                    // Objectif
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            $objUuidComposite = "obj_{$savoirFaire['pivot']['id']}_{$objectif['id']}";
                            $objUuidSimple = $objectif['uuid'] ?? '';
                            if ($type_element === 'objectif' && ($objUuidSimple == $uuid_element || $objUuidComposite == $uuid_element)) {
                                $this->clearProposition($objectif);
                                Log::info("✅ Proposition d'objectif effacée: {$objUuidSimple} / {$objUuidComposite}");
                                $found = true;
                                return;
                            }
                        }
                    }
                }
            }
        }

        if (!$found) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ POUR ANNULATION DE PROPOSITION: {$type_element} / {$uuid_element}");
        }
    }

    /**
     * Efface uniquement la proposition (ne touche pas à la validation).
     */
    protected function clearProposition(&$element)
    {
        $element['date_proposition'] = null;
        $element['commentaire'] = "";
        // On ne touche pas à etat_valid, ni à date_validation, ni au taux
        Log::info("Proposition effacée avec succès");
    }

    /**
     * Nettoie le commentaire.
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
}
