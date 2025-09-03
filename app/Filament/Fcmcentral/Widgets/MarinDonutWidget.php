<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;

class MarinRepartitionWidget extends ChartWidget
{
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
        $user = auth()->user();

          // Exclure les super_admin
          if ($user && $user->admin==1) {
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
                        $repartitionData['fonctions']['valides'],
                        $repartitionData['competences']['valides'],
                        $repartitionData['savoirfaires']['valides'],
                        $repartitionData['activites']['valides'],
                    ],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Non validés',
                    'data' => [
                        $repartitionData['fonctions']['non_valides'],
                        $repartitionData['competences']['non_valides'],
                        $repartitionData['savoirfaires']['non_valides'],
                        $repartitionData['activites']['non_valides'],
                    ],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Fonctions', 'Compétences', 'Savoir-faires', 'Activités'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function calculateRepartition(array $parcours): array
    {
        $stats = [
            'fonctions' => ['valides' => 0, 'non_valides' => 0],
            'competences' => ['valides' => 0, 'non_valides' => 0],
            'savoirfaires' => ['valides' => 0, 'non_valides' => 0],
            'activites' => ['valides' => 0, 'non_valides' => 0],
        ];

        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            // Vérifier si la fonction a au moins une compétence validée
            $fonctionACompetenceValidee = false;
            
            foreach ($fonction['competences'] ?? [] as $competence) {
                // Compétences
                if (($competence['etat_valid'] ?? false) === true) {
                    $stats['competences']['valides']++;
                    $fonctionACompetenceValidee = true;
                } else {
                    $stats['competences']['non_valides']++;
                }
                
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    // Savoir-faires
                    if (($savoirfaire['etat_valid'] ?? false) === true) {
                        $stats['savoirfaires']['valides']++;
                    } else {
                        $stats['savoirfaires']['non_valides']++;
                    }
                    
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        // Activités
                        if (($activite['etat_valid'] ?? false) === true) {
                            $stats['activites']['valides']++;
                        } else {
                            $stats['activites']['non_valides']++;
                        }
                    }
                }
            }
            
            // Fonctions
            if ($fonctionACompetenceValidee) {
                $stats['fonctions']['valides']++;
            } else {
                $stats['fonctions']['non_valides']++;
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
                                         (context.dataIndex < 4 ? 
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
