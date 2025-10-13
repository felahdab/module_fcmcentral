<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MentorStatsWidget extends BaseWidget
{
    
    use FcmAccessControl;
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';



    public static function canView(): bool
    {
        return static::canAccessMentorFeatures();
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

    // ✅ NOUVELLE STRUCTURE POUR LES WIDGETS MENTOR
private function calculateProgression(Marin $marin): array
{
    if (!$marin->complements_fcm) {
        return ['progress' => 0, 'total' => 0, 'completed' => 0];
    }

    $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
    if (!$parcours) {
        return ['progress' => 0, 'total' => 0, 'completed' => 0];
    }

    $totalSavoirfaires = 0;
    $savoirfairesValides = 0;
   
    foreach ($parcours['annees'] ?? [] as $annee) {
        foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
            $totalSavoirfaires++;
            if (($savoirfaire['etat_valid'] ?? false) === true) {
                $savoirfairesValides++;
            }
        }
    }

    $progress = $totalSavoirfaires > 0 ? round(($savoirfairesValides / $totalSavoirfaires) * 100) : 0;

    return [
        'progress' => $progress,
        'total' => $totalSavoirfaires,
        'completed' => $savoirfairesValides
    ];
}

private function calculateMarinStats($parcours): array
{
    $totalSavoirfaires = 0;
    $savoirfairesValides = 0;
    $enAttente = 0;
    $validationsSemaine = 0;

    foreach ($parcours['annees'] ?? [] as $annee) {
        foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
            $totalSavoirfaires++;
           
            if (($savoirfaire['etat_valid'] ?? false) === true) {
                $savoirfairesValides++;
               
                // ✅ Compter les validations de cette semaine
                if (!empty($savoirfaire['date_validation'])) {
                    $dateValidation = \Carbon\Carbon::parse($savoirfaire['date_validation']);
                    if ($dateValidation->isCurrentWeek()) {
                        $validationsSemaine++;
                    }
                }
            } else {
                // ✅ Logique pour détecter les savoir-faires en attente
                if (!empty($savoirfaire['date_proposition'])) {
                    $enAttente++;
                }
            }
        }
    }

    $progression = $totalSavoirfaires > 0 ? round(($savoirfairesValides / $totalSavoirfaires) * 100) : 0;

    return [
        'progression' => $progression,
        'en_attente' => $enAttente,
        'validations_semaine' => $validationsSemaine,
    ];
}

}
