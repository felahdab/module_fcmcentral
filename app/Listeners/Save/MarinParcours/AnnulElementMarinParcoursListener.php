<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;
use App\Models\User;
use Carbon\Carbon;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\Log;

class AnnulElementMarinParcoursListener
{
    public string $comment = '';

    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS)) {
            return;
        }

        $marin = Marin::where('uuid', $event->object_uuid)->first();
        if (!$marin) {
            Log::error('Marin non trouvé: ' . $event->object_uuid);
            return;
        }

        $data = $event->detail ?? [];
        $this->comment = ''; // Pour annulation, on vide le commentaire

        $annuleur = User::where('uuid', $event->user_uuid)->first();
        $annuleurName = $annuleur 
            ? ($annuleur->display_name ?? trim($annuleur->nom . ' ' . $annuleur->prenom))
            : 'ANNULEUR INCONNU';

        $donnees_du_parcours = $marin->complements_fcm->donnees_du_parcours ?? null;
        if (!$donnees_du_parcours) {
            Log::error('Données du parcours non trouvées pour le marin: ' . $marin->uuid);
            return;
        }

        $parcoursMarin = $donnees_du_parcours->parcoursmarin ?? null;
        if (!is_array($parcoursMarin)) {
            Log::error('Parcours marin non trouvé ou invalide: ' . $marin->uuid);
            return;
        }

        $type = $data['type_element'] ?? null;
        $uuid = $data['uuid_element'] ?? null;
        
        if (!$type || !$uuid) {
            Log::error('Payload invalide pour annulation: ' . json_encode($data));
            return;
        }

        Log::info("=== ANNULATION === type={$type} uuid={$uuid} marin={$marin->uuid}");

        // Gestion spéciale pour savoir-faire avec cascade
        if ($type === 'savoirfaire') {
            $updated = $this->annulSavoirFaireCascade($parcoursMarin, $uuid, $annuleurName);
        } else {
            $updated = $this->dispatchAnnulation($parcoursMarin, $type, $uuid, $annuleurName);
        }

        if (!$updated) {
            Log::error("❌ ÉLÉMENT NON TROUVÉ: {$type} / {$uuid}");
        }

        // Recalcul automatique des états parents (bottom-up)
        $this->recalculateParentStates($parcoursMarin);

        // Recalcul des heures
        CalculHeuresParcoursService::mettreAJourHeures($parcoursMarin);

        // Sauvegarde
        $donnees_du_parcours->taux_global = $parcoursMarin['taux_global'] ?? 0;
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();

        // Notification
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::ANNUL_ELMT_MARIN_PARCOURS,
            $marin->nom
        );

        Notification::make()
            ->title($notification['title'])
            ->warning()
            ->body($notification['message'])
            ->send();
    }

    protected function dispatchAnnulation(array &$parcoursMarin, string $type, string $uuid, string $displayName): bool
    {
        switch ($type) {
            case 'affectation':
                return $this->annulAffectation($parcoursMarin, $uuid, $displayName);
            
            case 'competence':
                return $this->annulCompetence($parcoursMarin, $uuid, $displayName);
            
            case 'objectif':
                return $this->annulObjectif($parcoursMarin, $uuid, $displayName);
            
            default:
                Log::warning("Type inconnu pour annulation: {$type}");
                return false;
        }
    }

    protected function annulAffectation(array &$parcoursMarin, string $uuid, string $displayName): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            if (($affectation['uuid'] ?? null) === $uuid) {
                $this->updateElement($affectation, $displayName);
                Log::info("✅ Affectation annulée: {$uuid}");
                return true;
            }
        }
        
        return false;
    }

    protected function annulCompetence(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                    Log::info("✅ Compétence annulée: {$uuid}");
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function annulSavoirFaireCascade(array &$parcoursMarin, string $uuid, string $displayName): bool
    {
        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            return false;
        }

        $objectifsAnnules = 0;

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
                        // Annuler le savoir-faire
                        $this->updateElement($csf, $displayName);
                        Log::info("✅ Savoir-faire annulé: {$uuid}");

                        // Annuler en cascade tous les objectifs validés
                        if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                            foreach ($csf['objectifs'] as &$objectif) {
                                if (($objectif['etat_valid'] ?? false)) {
                                    $this->updateElement($objectif, $displayName);
                                    $objectifsAnnules++;
                                    
                                    $objUuid = $objectif['uuid'] ?? ("obj_{$csf['id']}_{$objectif['id']}" ?? 'n/a');
                                    Log::info("  ✅ Objectif auto-annulé: {$objUuid}");
                                }
                            }
                        }

                        Log::info("RÉSULTAT: Savoir-faire annulé avec {$objectifsAnnules} objectifs en cascade");
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    protected function annulObjectif(array &$parcoursMarin, string $uuid, string $displayName): bool
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
                            Log::info("✅ Objectif annulé: {$uuid}");
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
                return $csf['uuid'] . '_' . $pivotPart;
            }
            return $csf['uuid'] ?? null;
        }

        // Legacy: csf avec savoirfaire['uuid'] + id
        if (!empty($csf['savoirfaire']['uuid']) && isset($csf['id'])) {
            return $csf['savoirfaire']['uuid'] . '_' . $csf['id'];
        }

        return $csf['uuid'] ?? null;
    }

    protected function recalculateParentStates(array &$parcoursMarin): void
    {
        Log::info("=== RECALCUL ÉTATS PARENTS ===");

        if (empty($parcoursMarin['affectations']) || !is_array($parcoursMarin['affectations'])) {
            Log::info("Aucune affectation pour recalcul");
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

        Log::info("✅ Recalcul terminé");
    }

    protected function updateElement(array &$element, string $displayName): void
    {
        $element['etat_valid'] = false;
        $element['commentaire'] = $this->comment;
        $element['date_validation'] = Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
        $element['date_proposition'] = null;
        
        Log::debug('Élément annulé avec succès');
    }
}
