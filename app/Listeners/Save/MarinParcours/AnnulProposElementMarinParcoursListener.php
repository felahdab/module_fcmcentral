<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class AnnulProposElementMarinParcoursListener
{
    public string $comment = '';

    /**
     * Gère l'événement d'annulation de proposition.
     * Efface uniquement la proposition, ne touche pas à la validation ni au parcours.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::where('uuid', $event->object_uuid)->first();
        if (!$marin) {
            Log::error('Marin non trouvé avec UUID: '.$event->object_uuid);

            return;
        }

        $data = $event->detail ?? [];
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');

        $annuleur = User::where('uuid', $event->user_uuid)->first();
        $annuleurName = $annuleur
            ? ($annuleur->display_name ?? trim($annuleur->nom.' '.$annuleur->prenom))
            : 'ANNULEUR INCONNU';

        $donnees_du_parcours = $marin->complements_fcm->donnees_du_parcours ?? null;
        if (!$donnees_du_parcours) {
            Log::error('Données du parcours non trouvées pour le marin: '.$marin->uuid);

            return;
        }

        $parcoursMarin = $donnees_du_parcours->parcoursmarin ?? null;
        if (!is_array($parcoursMarin)) {
            Log::error('Parcours marin non trouvé ou invalide: '.$marin->uuid);

            return;
        }

        $type = $data['type_element'] ?? null;
        $uuid = $data['uuid_element'] ?? null;

        if (!$type || !$uuid) {
            Log::error('Payload invalide pour annulation de proposition: '.json_encode($data));

            return;
        }

        Log::info("=== ANNULATION PROPOSITION === type={$type} uuid={$uuid} marin={$marin->uuid}");

        $updated = $this->effacerProposition($parcoursMarin, $type, $uuid);

        if (!$updated) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ POUR ANNULATION PROPOSITION: {$type} / {$uuid}");
        }

        // Sauvegarder (pas de recalcul de taux ni d'état)
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->warning()
            ->body($notification['message'])
            ->send()
        ;
    }

    /**
     * Efface la proposition sur l'élément ciblé selon le type.
     */
    protected function effacerProposition(array &$parcoursMarin, string $type, string $uuid): bool
    {
        switch ($type) {
            case 'affectation':
                return $this->clearPropositionAffectation($parcoursMarin, $uuid);

            case 'competence':
                return $this->clearPropositionCompetence($parcoursMarin, $uuid);

            case 'savoirfaire':
                return $this->clearPropositionSavoirFaire($parcoursMarin, $uuid);

            case 'objectif':
                return $this->clearPropositionObjectif($parcoursMarin, $uuid);

            default:
                Log::warning("Type inconnu pour annulation proposition: {$type}");

                return false;
        }
    }

    protected function clearPropositionAffectation(array &$parcoursMarin, string $uuid): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (($affectation['uuid'] ?? null) === $uuid) {
                $this->clearProposition($affectation);
                Log::info("✅ Proposition d'affectation effacée: {$uuid}");

                return true;
            }
        }

        return false;
    }

    protected function clearPropositionCompetence(array &$parcoursMarin, string $uuid): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (empty($affectation['competences']) || !is_array($affectation['competences'])) {
                continue;
            }

            foreach ($affectation['competences'] as &$competence) {
                if (($competence['uuid'] ?? null) === $uuid) {
                    $this->clearProposition($competence);
                    Log::info("✅ Proposition de compétence effacée: {$uuid}");

                    return true;
                }
            }
        }

        return false;
    }

    protected function clearPropositionSavoirFaire(array &$parcoursMarin, string $uuid): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (empty($affectation['competences']) || !is_array($affectation['competences'])) {
                continue;
            }

            foreach ($affectation['competences'] as &$competence) {
                if (empty($competence['competenceSavoirfaires']) || !is_array($competence['competenceSavoirfaires'])) {
                    continue;
                }

                foreach ($competence['competenceSavoirfaires'] as &$csf) {
                    $pivotUuid = $this->buildCsfPivotUuid($csf);

                    if (($csf['uuid'] ?? null) === $uuid || $pivotUuid === $uuid) {
                        $this->clearProposition($csf);
                        Log::info("✅ Proposition de savoir-faire effacée: {$uuid}");

                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function clearPropositionObjectif(array &$parcoursMarin, string $uuid): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (empty($affectation['competences']) || !is_array($affectation['competences'])) {
                continue;
            }

            foreach ($affectation['competences'] as &$competence) {
                if (empty($competence['competenceSavoirfaires']) || !is_array($competence['competenceSavoirfaires'])) {
                    continue;
                }

                foreach ($competence['competenceSavoirfaires'] as &$csf) {
                    if (empty($csf['objectifs']) || !is_array($csf['objectifs'])) {
                        continue;
                    }

                    foreach ($csf['objectifs'] as &$objectif) {
                        $objUuid = $objectif['uuid'] ?? ("obj_{$csf['id']}_{$objectif['id']}" ?? null);

                        if ($objUuid === $uuid || ($objectif['uuid'] ?? null) === $uuid) {
                            $this->clearProposition($objectif);
                            Log::info("✅ Proposition d'objectif effacée: {$uuid}");

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    protected function buildCsfPivotUuid(array $csf): ?string
    {
        $pivot = $csf['pivot'] ?? null;

        if ($pivot) {
            $pivotPart = $pivot['uuid'] ?? ($pivot['id'] ?? null);
            if ($pivotPart && !empty($csf['uuid'])) {
                return $csf['uuid'].'_'.$pivotPart;
            }

            return $csf['uuid'] ?? null;
        }

        // Legacy: csf avec savoirfaire['uuid'] + id
        if (!empty($csf['savoirfaire']['uuid']) && isset($csf['id'])) {
            return $csf['savoirfaire']['uuid'].'_'.$csf['id'];
        }

        return $csf['uuid'] ?? null;
    }

    /**
     * Efface uniquement la proposition (ne touche pas à la validation).
     */
    protected function clearProposition(array &$element): void
    {
        $element['date_proposition'] = null;
        $element['commentaire'] = $this->comment;
        // ✅ On ne touche PAS à :
        // - etat_valid
        // - date_validation
        // - valideur
        // - taux

        Log::debug('Proposition effacée avec succès');
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

        return trim($comment);
    }
}
