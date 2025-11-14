<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Traits\FcmPermissionTrait;

class MentorProgressionWidget extends ChartWidget
{
    use FcmPermissionTrait;

    protected static ?string $heading = 'Progression comparative des marins';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $minHeight = '400px';

    public static function canView(): bool
    {
        return static::canAccessModuleWithPermissions('widget.mentor');
    }

    protected function getData(): array
    {
        $currentMarin = Marin::fromCurrentUser();
        if (!$currentMarin) {
            return ['datasets' => [], 'labels' => []];
        }

        $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
            ->pluck('rh_marin_id')
            ->toArray();

        $marins = Marin::whereIn('id', $marinIds)
            ->with('lignes_dans_fcm_marin')
            ->get();

        $labels = [];
        $progressions = [];

        foreach ($marins as $marin) {
            $stats = $this->calculerStatistiques($marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? []);
            $labels[] = $marin->nom . ' ' . $marin->prenom;
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

    /**
     * Calcule les statistiques de progression, validés, proposés, non validés
     * sur la structure parcours['affectations']['competences']['competencesSavoirfaires']['objectifs']
     */
    private function calculerStatistiques(array $parcours): array
    {
        $total = 0;
        $valides = 0;
        $proposes = 0;

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competencesSavoirfaires'] ?? [] as $csf) {
                    $total++;
                    if ($this->verifierStatut($csf, 'valides')) $valides++;
                    if ($this->verifierStatut($csf, 'proposes')) $proposes++;

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $total++;
                        if ($this->verifierStatut($objectif, 'valides')) $valides++;
                        if ($this->verifierStatut($objectif, 'proposes')) $proposes++;
                    }
                }
            }
        }

        $progress = $total > 0 ? round(($valides / $total) * 100) : 0;

        return [
            'progress' => $progress,
            'total' => $total,
            'completed' => $valides,
            'proposes' => $proposes,
            'non_valides' => $total - $valides - $proposes,
        ];
    }

    /**
     * Vérifie le statut d'un élément (objectif ou savoir-faire)
     */
    private function verifierStatut(array $element, string $statutFilter = null): bool
    {
        $etatValid = ($element['etat_valid'] ?? false);
        $dateProposition = $element['date_proposition'] ?? null;

        $estValide = ($etatValid === true);
        $estPropose = !empty($dateProposition) && !$estValide;
        $nonValide = !$estValide && !$estPropose;

        return match($statutFilter) {
            'valides' => $estValide,
            'proposes' => $estPropose,
            'non_valides' => $nonValide,
            default => true
        };
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
