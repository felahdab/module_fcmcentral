<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MentorProgressionWidget extends ChartWidget
{
    
    use FcmAccessControl;
    protected static ?string $heading = 'Progression comparative des marins';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $minHeight = '400px';

    public static function canView(): bool
    {
        return static::canAccessMentorFeatures();
    }


    protected function getData(): array
    {
        $currentMarin = Marin::fromCurrentUser();
        
        if (!$currentMarin) {
            return ['datasets' => [], 'labels' => []];
        }
        
        // Corriger la requête pour utiliser la bonne relation
        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();
            
        $marins = Marin::whereIn('id', $marinIds)
            ->with('lignes_dans_fcm_marin') // Utiliser la vraie relation
            ->get();

        $labels = [];
        $progressions = [];

        foreach ($marins as $marin) {
            $stats = $this->calculateProgression($marin);
            $labels[] = $marin->nom . ' ' . $marin->prenom; // Utiliser les vraies propriétés
            $progressions[] = $stats['progress'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Progression (%)',
                    'data' => $progressions,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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


    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Progression (%)'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.parsed.y + "% de progression";
                        }'
                    ]
                ]
            ],
        ];
    }
}


