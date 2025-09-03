<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;

class MarinStatsWidget extends BaseWidget
{
    
    protected static ?int $sort = 2;

    public static function canView():bool
    {
        $user = auth()->user();

          // Exclure les super_admin
          if ($user->admin==1) {
            return false;
        }
        
        $marin = Marin::fromCurrentUser();
        
        // Accessible seulement aux marins FCM avec parcours
        return $marin !== null && 
               $marin->suivi_en_fcm &&
               $marin->complements_fcm !== null &&
               $marin->complements_fcm->donnees_du_parcours !== null &&
               $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null &&
               !$marin->isMentor();
    }
    
    
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
                
            Stat::make('Savoir-faires validés', $stats['savoirfaires_valides'] . '/' . $stats['total_savoirfaires'])
                ->description('Savoir-faires validés')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),
                
            Stat::make('Activités validées', $stats['activites_validees'] . '/' . $stats['total_activites'])
                ->description('Activités validées')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    private function calculateStats($parcours): array
    {
        $fonctions = count($parcours['fonctions'] ?? []);
        $competences = 0;
        $totalSavoirfaires = 0;
        $savoirfairesValides = 0;
        $totalActivites = 0;
        $activitesValidees = 0;

        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            $competences += count($fonction['competences'] ?? []);
            
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $totalSavoirfaires++;
                    if (($savoirfaire['etat_valid'] ?? false) === true) {
                        $savoirfairesValides++;
                    }
                    
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        if (($activite['etat_valid'] ?? false) === true) {
                            $activitesValidees++;
                        }
                    }
                }
            }
        }

        return [
            'fonctions' => $fonctions,
            'competences' => $competences,
            'total_savoirfaires' => $totalSavoirfaires,
            'savoirfaires_valides' => $savoirfairesValides,
            'total_activites' => $totalActivites,
            'activites_validees' => $activitesValidees,
        ];
    }
}
