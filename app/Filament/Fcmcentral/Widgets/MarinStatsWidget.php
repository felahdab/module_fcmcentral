<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinStatsWidget extends BaseWidget
{
    use FcmAccessControl;

    protected static ?int $sort = 2;

    public static function canView():bool
    {
       return static::canAccessMarinLivret();
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
            Stat::make('Années', $stats['annees'])
                ->description('Années dans votre parcours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
               
            Stat::make('Savoir-faires validés', $stats['savoirfaires_valides'] . '/' . $stats['total_savoirfaires'])
                ->description('Savoir-faires validés')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('success'),
               
            Stat::make('Taux global', $stats['taux_global'] . '%')
                ->description('Progression générale')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('info'),
               
            Stat::make('Dernière validation', $stats['derniere_validation'])
                ->description('Date de la dernière validation')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),
        ];
    }

    private function calculateStats($parcours): array
    {
        $annees = count($parcours['annees'] ?? []);
        $totalSavoirfaires = 0;
        $savoirfairesValides = 0;
        $derniereValidation = null;
       
        foreach ($parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $totalSavoirfaires++;
                if (($savoirfaire['etat_valid'] ?? false) === true) {
                    $savoirfairesValides++;
                   
                    // ✅ Suivre la dernière validation
                    if (!empty($savoirfaire['date_validation'])) {
                        $dateValidation = \Carbon\Carbon::parse($savoirfaire['date_validation']);
                        if (!$derniereValidation || $dateValidation->gt($derniereValidation)) {
                            $derniereValidation = $dateValidation;
                        }
                    }
                }
            }
        }

        // ✅ Calculer le taux global sur les savoir-faires
        $tauxGlobal = $totalSavoirfaires > 0 ? round(($savoirfairesValides / $totalSavoirfaires) * 100) : 0;

        return [
            'annees' => $annees,
            'total_savoirfaires' => $totalSavoirfaires,
            'savoirfaires_valides' => $savoirfairesValides,
            'taux_global' => $tauxGlobal,
            'derniere_validation' => $derniereValidation ? $derniereValidation->diffForHumans() : 'Aucune',
        ];
    }
}
