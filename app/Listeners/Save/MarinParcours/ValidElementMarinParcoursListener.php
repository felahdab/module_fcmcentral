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
use Modules\FcmCommun\Services\RefaireEtatParcoursService;

class ValidElementMarinParcoursListener
{
    public string $comment = '';
    public Carbon $dateValidation;
    public ?string $pieceJointe = null;

    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::findByEventReference($event->object_uuid);
        if (!$marin) {
            Log::error('Marin non trouvé: '.$event->object_uuid);

            return;
        }

        $data = $event->detail ?? [];
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');
        $this->pieceJointe = $data['piece_jointe'] ?? null;

        // Date pour antidater
        $this->dateValidation = !empty($data['date'])
        ? Carbon::parse($data['date'])
        : Carbon::parse($event->event_date ?? now());

        $valideur = User::where('uuid', $event->user_uuid)->first();
        $valideurName = $valideur
            ? ($valideur->display_name ?? trim($valideur->nom.' '.$valideur->prenom))
            : 'VALIDEUR INCONNU';

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
            Log::error('Payload invalide pour validation: '.json_encode($data));

            return;
        }

        Log::info("=== VALIDATION === type={$type} uuid={$uuid} marin={$marin->uuid}");

        $updated = $this->dispatchValidation($parcoursMarin, $type, $uuid, $valideurName);

        if (!$updated) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ: {$type} / {$uuid}");
        }

        // Recalcul automatique des états parents (bottom-up)
        // $this->recalculateParentStates($parcoursMarin);

        // Refonte Parcours
        // Log::info("AVANT RefaireEtatParcoursService", ['parcours' => $parcoursMarin]);
        RefaireEtatParcoursService::refonte($parcoursMarin);
        // Log::info("APRÈS RefaireEtatParcoursService", ['parcours' => $parcoursMarin]);

        // Recalcul des heures
        CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);
        // Log::info("APRÈS CalculHeuresParcoursService", ['parcours' => $parcoursMarin]);

        // Sauvegarde
        $donnees_du_parcours->taux_global = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcoursMarin) ?? 0;
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::VALID_ELMT_MARIN_PARCOURS,
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

    protected function dispatchValidation(array &$parcoursMarin, string $type, string $uuid, string $displayName): bool
    {
        switch ($type) {
            case 'affectation':
                return $this->validateAffectation($parcoursMarin, $uuid, $displayName);

            case 'competence':
                return $this->validateCompetence($parcoursMarin, $uuid, $displayName);

            case 'savoirfaire':
                return $this->validateSavoirFaire($parcoursMarin, $uuid, $displayName);

            case 'objectif':
                return $this->validateObjectif($parcoursMarin, $uuid, $displayName);

            default:
                Log::warning("Type inconnu pour validation: {$type}");

                return false;
        }
    }

    protected function validateAffectation(array &$parcoursMarin, string $uuid, string $displayName): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (($affectation['uuid'] ?? null) === $uuid) {
                $this->updateElement($affectation, $displayName);
                Log::info("✅ Affectation validée: {$uuid}");

                return true;
            }
        }

        return false;
    }

    protected function validateCompetence(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                    Log::info("✅ Compétence validée: {$uuid}");

                    return true;
                }
            }
        }

        return false;
    }

    protected function validateSavoirFaire(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                        Log::info("✅ Savoir-faire validé: {$uuid}");

                        // Auto-validation des objectifs si type compagnonnage
                        if (($csf['savoirfaire_type']['code'] ?? null) === 'C') {
                            if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                                foreach ($csf['objectifs'] as &$objectif) {
                                    if (!($objectif['etat_valid'] ?? false)) {
                                        $this->updateElement($objectif, $displayName);
                                        Log::info('  ✅ Objectif auto-validé: '.($objectif['uuid'] ?? 'n/a'));
                                    }
                                }
                            }
                        }

                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function validateObjectif(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                            Log::info("✅ Objectif validé: {$uuid}");

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

    protected function recalculateParentStates(array &$parcoursMarin): void
    {
        Log::info('=== RECALCUL ÉTATS PARENTS ===');

        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            Log::info('Aucune affectation pour recalcul');

            return;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (empty($affectation['competences']) || !is_array($affectation['competences'])) {
                $affectation['etat_valid'] = false;

                continue;
            }

            foreach ($affectation['competences'] as &$competence) {
                if (empty($competence['competenceSavoirfaires']) || !is_array($competence['competenceSavoirfaires'])) {
                    $competence['etat_valid'] = false;

                    continue;
                }

                // Recalcul savoir-faire basé sur objectifs
                foreach ($competence['competenceSavoirfaires'] as &$csf) {
                    if (!empty($csf['objectifs']) && is_array($csf['objectifs']) && count($csf['objectifs']) > 0) {
                        $allValid = true;
                        foreach ($csf['objectifs'] as $obj) {
                            if (!($obj['etat_valid'] ?? false)) {
                                $allValid = false;

                                break;
                            }
                        }
                        $csf['etat_valid'] = $allValid;
                    }
                }

                // Recalcul compétence basé sur savoir-faire
                $allCsfValid = true;
                foreach ($competence['competenceSavoirfaires'] as $csf) {
                    if (!($csf['etat_valid'] ?? false)) {
                        $allCsfValid = false;

                        break;
                    }
                }
                $competence['etat_valid'] = $allCsfValid;
            }

            // Recalcul affectation basé sur compétences
            $allCompValid = true;
            foreach ($affectation['competences'] as $comp) {
                if (!($comp['etat_valid'] ?? false)) {
                    $allCompValid = false;

                    break;
                }
            }
            $affectation['etat_valid'] = $allCompValid;
        }

        Log::info('✅ Recalcul terminé');
    }

    protected function updateElement(array &$element, string $displayName): void
    {
        $element['etat_valid'] = true;
        $element['commentaire'] = $this->comment;
        // $element['date_validation'] = Carbon::now()->toDateTimeString();
        $element['date_validation'] = $this->dateValidation->toDateTimeString();
        $element['valideur'] = $displayName;
        $element['date_proposition'] = null;

        if ($this->pieceJointe) {
            $element['piece_jointe'] = $this->pieceJointe;
        }

        Log::debug('Élément validé avec succès');
    }
}
