<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Carbon\Carbon;

class MarinEvolutionWidget extends ChartWidget
{
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
                    'label' => 'Activités validées (cumul)',
                    'data' => $evolutionData['activites_cumul'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
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
                    'label' => 'Objectif activités',
                    'data' => $evolutionData['objectif_activites'],
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
                'activites_cumul' => [], 
                'savoirfaires_cumul' => [],
                'objectif_activites' => [],
                'moyenne_mobile' => [],
                'labels' => []
            ];
        }

        // Calculer le total d'activités et savoir-faires
        $totalActivites = 0;
        $totalSavoirfaires = 0;
        $validations = [];
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $totalSavoirfaires++;
                    
                    // Savoir-faire validé
                    if (($savoirfaire['etat_valid'] ?? false) === true && 
                        !empty($savoirfaire['date_validation'])) {
                        $validations[] = [
                            'date' => Carbon::parse($savoirfaire['date_validation']),
                            'type' => 'savoirfaire'
                        ];
                    }
                    
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        
                        // Activité validée
                        if (($activite['etat_valid'] ?? false) === true && 
                            !empty($activite['date_validation'])) {
                            $validations[] = [
                                'date' => Carbon::parse($activite['date_validation']),
                                'type' => 'activite'
                            ];
                        }
                    }
                }
            }
        }

        if (empty($validations)) {
            return [
                'activites_cumul' => [], 
                'savoirfaires_cumul' => [],
                'objectif_activites' => [],
                'moyenne_mobile' => [],
                'labels' => []
            ];
        }

        // Trier par date
        usort($validations, function($a, $b) {
            return $a['date']->timestamp - $b['date']->timestamp;
        });

        // Générer les données mensuelles
        $startDate = $validations[0]['date']->copy()->startOfMonth();
        $endDate = Carbon::now()->addMonths(3)->endOfMonth(); // Projeter 3 mois dans le futur
        
        $labels = [];
        $activitesCumul = [];
        $savoirfairesCumul = [];
        $objectifActivites = [];
        $moyenneMobile = [];
        
        $currentDate = $startDate->copy();
        $dureeEnMois = $startDate->diffInMonths($endDate);
        
        while ($currentDate <= $endDate) {
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            // Calculer les cumuls
            $activitesCumulees = 0;
            $savoirfairesCumules = 0;
            
            foreach ($validations as $validation) {
                if ($validation['date'] <= $monthEnd) {
                    if ($validation['type'] === 'activite') {
                        $activitesCumulees++;
                    } else {
                        $savoirfairesCumules++;
                    }
                }
            }
            
            // Objectif linéaire basé sur le total
            $moisEcoules = $startDate->diffInMonths($currentDate) + 1;
            $objectif = round(($totalActivites * $moisEcoules) / $dureeEnMois);
            
            $labels[] = $currentDate->format('M Y');
            $activitesCumul[] = $activitesCumulees;
            $savoirfairesCumul[] = $savoirfairesCumules;
            $objectifActivites[] = min($objectif, $totalActivites);
            
            $currentDate->addMonth();
        }
        
        // Calculer la moyenne mobile sur 3 mois
        for ($i = 0; $i < count($activitesCumul); $i++) {
            if ($i < 2) {
                $moyenneMobile[] = $activitesCumul[$i];
            } else {
                $moyenne = ($activitesCumul[$i-2] + $activitesCumul[$i-1] + $activitesCumul[$i]) / 3;
                $moyenneMobile[] = round($moyenne, 1);
            }
        }

        return [
            'activites_cumul' => $activitesCumul,
            'savoirfaires_cumul' => $savoirfairesCumul,
            'objectif_activites' => $objectifActivites,
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
                        'text' => 'Nombre d\'éléments',
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
