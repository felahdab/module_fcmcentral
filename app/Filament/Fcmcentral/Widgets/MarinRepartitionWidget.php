<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinRepartitionWidget extends ChartWidget
{
    use FcmAccessControl;
   
    protected static ?string $heading = 'Répartition du parcours';
   
    protected int | string | array $columnSpan = [
        'sm' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
    ];
   
    protected static ?int $sort = 4;

    public static function canView():bool
    {
       return static::canAccessMarinLivret();
    }

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
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

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
       
        if (!$parcours) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $repartitionData = $this->calculateRepartition($parcours);

        return [
            'datasets' => [
                [
                    'label' => 'Validés',
                    'data' => [
                        $repartitionData['annees']['valides'],
                        $repartitionData['savoirfaires']['valides'],
                    ],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Non validés',
                    'data' => [
                        $repartitionData['annees']['non_valides'],
                        $repartitionData['savoirfaires']['non_valides'],
                    ],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Années', 'Savoir-faires'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function calculateRepartition(array $parcours): array
    {
        $stats = [
            'annees' => ['valides' => 0, 'non_valides' => 0],
            'savoirfaires' => ['valides' => 0, 'non_valides' => 0],
        ];

        foreach ($parcours['annees'] ?? [] as $annee) {
            // ✅ Vérifier si l'année a au moins un savoir-faire validé
            $anneeASavoirFaireValide = false;
           
            foreach ($annee['savoirfaires'] ?? [] as $savoirfaire) {
                // ✅ Savoir-faires
                if (($savoirfaire['etat_valid'] ?? false) === true) {
                    $stats['savoirfaires']['valides']++;
                    $anneeASavoirFaireValide = true;
                } else {
                    $stats['savoirfaires']['non_valides']++;
                }
            }
           
            // ✅ Années
            if ($anneeASavoirFaireValide) {
                $stats['annees']['valides']++;
            } else {
                $stats['annees']['non_valides']++;
            }
        }

        return $stats;
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
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
                    'callbacks' => [
                        'afterLabel' => 'function(context) {
                            const total = context.dataset.data[context.dataIndex] +
                                         (context.dataIndex < 2 ?
                                          context.chart.data.datasets[context.datasetIndex === 0 ? 1 : 0].data[context.dataIndex] : 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return "(" + percentage + "%)";
                        }'
                    ]
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Éléments du parcours',
                        'font' => ['weight' => 'bold']
                    ],
                    'grid' => [
                        'display' => false,
                    ]
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre',
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
            ]
        ];
    }
}
