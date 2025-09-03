<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

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
        $user = auth()->user();
        
        // Exclure les admin (user->admin == 1)
        if ($user && $user->admin == 1) {
            return false;
        }
        
        $marin = Marin::fromCurrentUser();
        
        return Marin::estMentor($marin);
    }

    protected function getStats(): array
    {
        $marinsSupervises = $this->getMarinsSupervises();
        $stats = $this->calculateMentorStats($marinsSupervises);

        return [
            Stat::make('Marins supervisés', $stats['total_marins'])
                ->description('Marins sous votre supervision')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Activités en attente', $stats['activites_en_attente'])
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
        
        // Récupérer les IDs via FcmMarin puis les marins avec leurs relations
        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();
        
        return Marin::whereIn('id', $marinIds)
            ->with('lignes_dans_fcm_marin') // Utiliser la vraie relation
            ->get();
    }
    


    private function calculateMentorStats($marins): array
    {
        $totalMarins = $marins->count();
        $activitesEnAttente = 0;
        $progressions = [];
        $validationsSemaine = 0;

        foreach ($marins as $marin) {
            if (!$marin->complements_fcm) continue;
            
            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
            if (!$parcours) continue;

            $marinStats = $this->calculateMarinStats($parcours);
            $progressions[] = $marinStats['progression'];
            $activitesEnAttente += $marinStats['en_attente'];
            $validationsSemaine += $marinStats['validations_semaine'];
        }

        $progressionMoyenne = count($progressions) > 0 ? round(array_sum($progressions) / count($progressions)) : 0;

        return [
            'total_marins' => $totalMarins,
            'activites_en_attente' => $activitesEnAttente,
            'progression_moyenne' => $progressionMoyenne,
            'validations_semaine' => $validationsSemaine,
        ];
    }

    private function calculateMarinStats($parcours): array
    {
        $totalActivites = 0;
        $activitesValidees = 0;
        $enAttente = 0;
        $validationsSemaine = 0;

        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        
                        if (($activite['etat_valid'] ?? false) === true) {
                            $activitesValidees++;
                            
                            // Compter les validations de cette semaine
                            if (!empty($activite['date_validation'])) {
                                $dateValidation = \Carbon\Carbon::parse($activite['date_validation']);
                                if ($dateValidation->isCurrentWeek()) {
                                    $validationsSemaine++;
                                }
                            }
                        } else {
                            // Logique pour détecter les activités en attente
                            if (!empty($activite['date_proposition'])) {
                                $enAttente++;
                            }
                        }
                    }
                }
            }
        }

        $progression = $totalActivites > 0 ? round(($activitesValidees / $totalActivites) * 100) : 0;

        return [
            'progression' => $progression,
            'en_attente' => $enAttente,
            'validations_semaine' => $validationsSemaine,
        ];
    }
}
