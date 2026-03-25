<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin;

use Carbon\Carbon;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\FcmCentral\Models\Marin;

class MarinStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        // $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
        // TODO: faut-il limiter davantage l'accès ? Certains marin disposant
        // d'un parcours doivent-ils être empéchés de voir ce widget ?
       //  return $marin?->complements_fcm?->donnees_du_parcours != null;
       return false;
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
            Stat::make('Affectations', $stats['affectations'])
                ->description('Affectations dans votre parcours')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
           
            Stat::make('Heures totales', $stats['heures_total'] . 'h')
                ->description($stats['heures_validees'] . 'h validées')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
           
            Stat::make('Stage', $stats['heures_stage'] . 'h')
                ->description('Heures en stage (S)')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
           
            Stat::make('Compagnonnage', $stats['heures_compagnonnage'] . 'h')
                ->description('Heures en compagnonnage (C)')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Taux global', $stats['taux_global'] . '%')
                ->description('Progression générale')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('secondary'),
           
            Stat::make('Dernière validation', $stats['derniere_validation'])
                ->description('Date de la dernière validation')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('danger'),
        ];
    }

    private function calculateStats(array $parcours): array
    {
        $nbAffectations = count($parcours['affectations'] ?? []);

        // ✅ Heures globales : lire les valeurs déjà calculées par CalculHeuresParcoursService
        $heuresTotal    = 0;
        $heuresValidees = 0;
        foreach ($parcours['affectations'] ?? [] as $affectation) {
            $heuresTotal    += $affectation['heures_total']    ?? 0;
            $heuresValidees += $affectation['heures_validees'] ?? 0;
        }

        // ✅ Répartition par type + dernière validation (non fournis par le service)
        $heuresStage         = 0;
        $heuresCompagnonnage = 0;
        $heuresEad           = 0;
        $derniereValidation  = null;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {

                    $heures = CalculHeuresParcoursService::getHeures($csf);

                    $type = $csf['savoirfaire_type']['code']
                        ?? $csf['savoirfaireType']['code']
                        ?? $csf['type_code']
                        ?? null;

                    if ($type === 'S')                          $heuresStage         += $heures;
                    elseif ($type === 'C')                      $heuresCompagnonnage += $heures;
                    elseif (in_array($type, ['E', 'EAD', 'D'])) $heuresEad           += $heures;

                    // Dernière validation (CSF ou objectifs enfants)
                    $elementsAControler = array_merge([$csf], $csf['objectifs'] ?? []);
                    foreach ($elementsAControler as $el) {
                        if (($el['etat_valid'] ?? false) && !empty($el['date_validation'])) {
                            $date = Carbon::parse($el['date_validation']);
                            if (!$derniereValidation || $date->gt($derniereValidation)) {
                                $derniereValidation = $date;
                            }
                        }
                    }
                }
            }
        }

        // ✅ Taux basé sur les heures (compagnonnage = objectifs) via le service
        $tauxGlobal = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcours);

        return [
            'affectations'        => $nbAffectations,
            'heures_total'        => round($heuresTotal, 0),
            'heures_validees'     => round($heuresValidees, 0),
            'heures_stage'        => round($heuresStage, 0),
            'heures_compagnonnage'=> round($heuresCompagnonnage, 0),
            'heures_ead'          => round($heuresEad, 0),
            'taux_global'         => $tauxGlobal,
            'derniere_validation' => $derniereValidation ? $derniereValidation->diffForHumans() : 'Aucune',
        ];
    }


    
}
