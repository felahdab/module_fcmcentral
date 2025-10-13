<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Carbon\Carbon;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinEvolutionWidget extends ChartWidget
{
    use FcmAccessControl;
   
    protected static ?string $heading = 'Évolution détaillée du parcours';
   
    protected int | string | array $columnSpan = 'full';
   
    protected static ?string $maxHeight = '400px';
   
    protected static ?int $sort = 3;

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
    }

    public static function canView():bool
    {
       return static::canAccessMarinLivret();
    }

    protected function getData(): array
    {
        $marin = $this->getMarin();
       
        if (!$marin || !$marin->complements_fcm) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $evolutionData = $this->calculateDetailedEvolution();

        return [
            'datasets' => [
                [
                    'label' => 'Savoir-faires validés (cumul)',
                    'data' => $evolutionData['savoirfaires_cumul'],
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
                [
                    'label' => 'Objectif savoir-faires',
                    'data' => $evolutionData['objectif_savoirfaires'],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0,
                    'pointRadius' => 0,
                ],
                [
                    'label' => 'Moyenne mobile (3 mois)',
                    'data' => $evolutionData['moyenne_mobile'],
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => false,
                    'tension' => 0.6,
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $evolutionData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function calculateDetailedEvolution(): array
    {
        $marin = $this->getMarin();
        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
       
        if (!$parcours) {
            return [
                'savoirfaires_cumul' => [],
                'objectif_savoirfaires' => [],
                'moyenne_mobile' => [],
                'labels' => []
            ];
        }

        // ✅ Calculer le total de savoir-faires et récupérer les validations
        $totalSavoirfaires = 0;
        $validations = [];
       
        foreach ($parcours['annees'] ?? [] as $annee) {
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                $totalSavoirfaires++;
               
                // ✅ Savoir-faire validé
                if (($savoirfaire['etat_valid'] ?? false) === true &&
                    !empty($savoirfaire['date_validation'])) {
                    $validations[] = [
                        'date' => Carbon::parse($savoirfaire['date_validation']),
                        'type' => 'savoirfaire'
                    ];
                }
            }
        }

        if (empty($validations)) {
            return [
                'savoirfaires_cumul' => [],
                'objectif_savoirfaires' => [],
                'moyenne_mobile' => [],
                'labels' => []
            ];
        }

        // ✅ Trier par date
        usort($validations, function($a, $b) {
            return $a['date']->timestamp - $b['date']->timestamp;
        });

        // ✅ Générer les données mensuelles
        $startDate = $validations[0]['date']->copy()->startOfMonth();
        $endDate = Carbon::now()->addMonths(3)->endOfMonth();
       
        $labels = [];
        $savoirfairesCumul = [];
        $objectifSavoirfaires = [];
        $moyenneMobile = [];
       
        $currentDate = $startDate->copy();
        $dureeEnMois = $startDate->diffInMonths($endDate);
       
        while ($currentDate <= $endDate) {
            $monthEnd = $currentDate->copy()->endOfMonth();
           
            // ✅ Calculer les cumuls
            $savoirfairesCumules = 0;
           
            foreach ($validations as $validation) {
                if ($validation['date'] <= $monthEnd && $validation['type'] === 'savoirfaire') {
                    $savoirfairesCumules++;
                }
            }
           
            // ✅ Objectif linéaire basé sur le total
            $moisEcoules = $startDate->diffInMonths($currentDate) + 1;
            $objectif = round(($totalSavoirfaires * $moisEcoules) / $dureeEnMois);
           
            $labels[] = $currentDate->format('M Y');
            $savoirfairesCumul[] = $savoirfairesCumules;
            $objectifSavoirfaires[] = min($objectif, $totalSavoirfaires);
           
            $currentDate->addMonth();
        }

        // ✅ Calculer la moyenne mobile sur 3 mois
        for ($i = 0; $i < count($savoirfairesCumul); $i++) {
            if ($i < 2) {
                $moyenneMobile[] = $savoirfairesCumul[$i];
            } else {
                $moyenne = ($savoirfairesCumul[$i-2] + $savoirfairesCumul[$i-1] + $savoirfairesCumul[$i]) / 3;
                $moyenneMobile[] = round($moyenne, 1);
            }
        }

        return [
            'savoirfaires_cumul' => $savoirfairesCumul,
            'objectif_savoirfaires' => $objectifSavoirfaires,
            'moyenne_mobile' => $moyenneMobile,
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Période',
                        'font' => ['weight' => 'bold']
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ]
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre de savoir-faires',
                        'font' => ['weight' => 'bold']
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'callback' => 'function(value) { return Math.floor(value) === value ? value : null; }'
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ]
                ]
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false
            ],
            'elements' => [
                'point' => [
                    'hoverBackgroundColor' => 'white',
                    'hoverBorderWidth' => 2,
                ]
            ]
        ];
    }
}
