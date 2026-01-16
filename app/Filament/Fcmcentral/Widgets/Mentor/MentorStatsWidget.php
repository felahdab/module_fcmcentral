<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;

class MentorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Ce widget ne doit être visible que si l'utilisateur est un mentor.
        $currentMarin = Marin::fromCurrentUser();
        if (!$currentMarin) return false;

        return $currentMarin->est_mentor ;
    }

    protected function getStats(): array
    {
        $marinsSupervises = $this->getMarinsSupervises();
        $stats = $this->calculerMentorStats($marinsSupervises);

        return [
            Stat::make('Marins supervisés', $stats['total_marins'])
                ->description('Marins sous votre supervision')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Activités en attente', $stats['propose'])
                ->description('À valider')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Progression moyenne', $stats['progression_moyenne'] . '%')
                ->description('Moyenne de tous vos marins')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Validations cette semaine', $stats['validations_semaine'])
                ->description('Activités validées')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }

    private function getMarinsSupervises(): \Illuminate\Support\Collection
    {
        $currentMarin = Marin::fromCurrentUser();
        if (!$currentMarin) {
            return collect();
        }

        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();

        return Marin::whereIn('id', $marinIds)
            ->with('lignes_dans_fcm_marin')
            ->get();
    }

    private function calculerMentorStats($marins): array
    {
        $totalMarins = $marins->count();
        $progressions = [];
        $propose = 0;
        $validationsSemaine = 0;

        foreach ($marins as $marin) {
            if (!$marin->complements_fcm) continue;
            
            // ✅ Passer le parcours en paramètre
            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];
            $stats = $this->calculerStatistiques($parcours);
            
            $progressions[] = $stats['progress'];
            $propose += $stats['proposes'];
            $validationsSemaine += $stats['validations_semaine'];
        }

        $progressionMoyenne = count($progressions) > 0 ? round(array_sum($progressions) / count($progressions)) : 0;

        return [
            'total_marins' => $totalMarins,
            'propose' => $propose,
            'progression_moyenne' => $progressionMoyenne,
            'validations_semaine' => $validationsSemaine,
        ];
    }

    /**
     * Calcule les statistiques de progression, validés, proposés, non validés
     * sur la structure parcours['affectations']['competences']['competencesSavoirfaires']['objectifs']
     */
    private function calculerStatistiques(array $parcours): array
    {
        $total = 0;
        $valides = 0;
        $proposes = 0;
        $validationsSemaine = 0;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $total++;
                   
                    if ($csf['etat_valid'] ?? false) {
                        $valides++;
                        if (!empty($csf['date_validation'])) {
                            $dateValidation = \Carbon\Carbon::parse($csf['date_validation']);
                            if ($dateValidation->isCurrentWeek()) {
                                $validationsSemaine++;
                            }
                        }
                    }
                    if (!empty($csf['date_proposition'])) {
                        $proposes++;
                    }

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $total++;
                       
                        if ($objectif['etat_valid'] ?? false) {
                            $valides++;
                            if (!empty($objectif['date_validation'])) {
                                $dateValidation = \Carbon\Carbon::parse($objectif['date_validation']);
                                if ($dateValidation->isCurrentWeek()) {
                                    $validationsSemaine++;
                                }
                            }
                        }
                        if (!empty($objectif['date_proposition'])) {
                            $proposes++;
                        }
                    }
                }
            }
        }

        $progress = $total > 0 ? round(($valides / $total) * 100) : 0;

        return [
            'progress' => $progress,
            'total' => $total,
            'valides' => $valides,
            'proposes' => $proposes,
            'validations_semaine' => $validationsSemaine,
            'non_valides' => $total - $valides
        ];
    }
}
