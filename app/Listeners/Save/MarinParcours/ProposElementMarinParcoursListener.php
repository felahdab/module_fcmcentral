<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class ProposElementMarinParcoursListener
{
    public string $comment = '';

    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::findByEventReference($event->object_uuid);
        if (!$marin) {
            Log::error('Marin non trouvé: '.$event->object_uuid);

            return;
        }

        $data = $event->detail ?? [];
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');

        $proposeur = User::where('uuid', $event->user_uuid)->first();
        $proposeurName = $proposeur
            ? ($proposeur->display_name ?? trim($proposeur->nom.' '.$proposeur->prenom))
            : 'PROPOSEUR INCONNU';

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
            Log::error('Payload invalide pour proposition: '.json_encode($data));

            return;
        }

        Log::info("=== PROPOSITION === type={$type} uuid={$uuid} marin={$marin->uuid}");

        $updated = $this->dispatchProposition($parcoursMarin, $type, $uuid, $proposeurName);

        if (!$updated) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ: {$type} / {$uuid}");
        }

        // Recalcul des heures
        CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);

        // Sauvegarde
        // $donnees_du_parcours->taux_global = $parcoursMarin['taux_global'] ?? 0;
        $donnees_du_parcours->taux_global = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcoursMarin) ?? 0;
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::PROPOS_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send()
        ;
    }

    protected function sanitizeComment(string $comment): string
    {
        if ('' === trim($comment)) {
            return '';
        }

        $comment = strip_tags($comment);
        $comment = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $comment);
        $comment = preg_replace('/\s+/', ' ', $comment);

        return addslashes(trim($comment));
        // return trim($comment);
    }

    protected function dispatchProposition(array &$parcoursMarin, string $type, string $uuid, string $displayName): bool
    {
        switch ($type) {
            case 'affectation':
                return $this->proposeAffectation($parcoursMarin, $uuid, $displayName);

            case 'competence':
                return $this->proposeCompetence($parcoursMarin, $uuid, $displayName);

            case 'savoirfaire':
                return $this->proposeSavoirFaire($parcoursMarin, $uuid, $displayName);

            case 'objectif':
                return $this->proposeObjectif($parcoursMarin, $uuid, $displayName);

            default:
                Log::warning("Type inconnu pour proposition: {$type}");

                return false;
        }
    }

    protected function proposeAffectation(array &$parcoursMarin, string $uuid, string $displayName): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (($affectation['uuid'] ?? null) === $uuid) {
                $this->updateElement($affectation, $displayName);
                Log::info("✅ Affectation proposée: {$uuid}");

                return true;
            }
        }

        return false;
    }

    protected function proposeCompetence(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                    $this->updateElement($competence, $displayName);
                    Log::info("✅ Compétence proposée: {$uuid}");

                    return true;
                }
            }
        }

        return false;
    }

    protected function proposeSavoirFaire(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                        $this->updateElement($csf, $displayName);
                        Log::info("✅ Savoir-faire proposé: {$uuid}");

                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function proposeObjectif(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                            $this->updateElement($objectif, $displayName);
                            Log::info("✅ Objectif proposé: {$uuid}");

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

    protected function updateElement(array &$element, string $displayName): void
    {
        $element['commentaire_proposition'] = $this->comment;
        $element['date_proposition'] = Carbon::now()->toDateTimeString();
        $element['etat_propos'] = true;

        Log::debug('Élément proposé avec succès');
    }
}
