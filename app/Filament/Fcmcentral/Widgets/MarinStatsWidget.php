<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;

class MarinStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $marin = Marin::fromCurrentUser();
        
        if (!$marin || !$marin->complements_fcm) {
            return [];
        }

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
        
        if (!$parcours) {
            return [];
        }

        $stats = $this->calculateStats($parcours);

        return [
            Stat::make('Fonctions', $stats['fonctions'])
                ->description('Fonctions dans votre parcours')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
                
            Stat::make('Compétences', $stats['competences'])
                ->description('Compétences à acquérir')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
                
            Stat::make('Activités validées', $stats['activites_validees'] . '/' . $stats['total_activites'])
                ->description('Progression des activités')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    private function calculateStats($parcours): array
    {
        $fonctions = count($parcours['fonctions'] ?? []);
        $competences = 0;
        $totalActivites = 0;
        $activitesValidees = 0;

        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            $competences += count($fonction['competences'] ?? []);
            
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        if (($activite['etat_validation'] ?? '') === 'valide') {
                            $activitesValidees++;
                        }
                    }
                }
            }
        }

        return [
            'fonctions' => $fonctions,
            'competences' => $competences,
            'total_activites' => $totalActivites,
            'activites_validees' => $activitesValidees
        ];
    }
}
