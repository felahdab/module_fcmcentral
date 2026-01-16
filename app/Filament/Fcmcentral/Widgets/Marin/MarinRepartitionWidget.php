<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;

class MarinRepartitionWidget extends ChartWidget
{
    protected static ?string $heading = 'Répartition des savoir-faire par type';
   
    protected int | string | array $columnSpan = [
        'sm' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
    ];
   
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
        // TODO: faut-il limiter davantage l'accès ? Certains marin disposant
        // d'un parcours doivent-ils être empéchés de voir ce widget ?
        return $marin?->complements_fcm?->donnees_du_parcours != null;
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
                    'label' => 'Stage (S)',
                    'data' => $repartitionData['stage'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Compagnonnage (C)',
                    'data' => $repartitionData['compagnonnage'],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'EAD',
                    'data' => $repartitionData['ead'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $repartitionData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function calculateRepartition(array $parcours): array
    {
        $labels = [];
        $dataStage = [];
        $dataCompagnonnage = [];
        $dataEad = [];

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            $labels[] = $affectation['libelle_court'] ??
                        $affectation['code'] ??
                        substr($affectation['uuid'] ?? 'AFF', 0, 10);

            $countS = $countC = $countE = 0;

            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $type = $csf['savoirfaire_type']['code'] ??
                            $csf['savoirfaireType']['code'] ??
                            $csf['type_code'] ?? null;

                    if ($type === 'S') $countS++;
                    elseif ($type === 'C') $countC++;
                    elseif (in_array($type, ['E', 'EAD', 'D'])) $countE++;
                }
            }

            $dataStage[] = $countS;
            $dataCompagnonnage[] = $countC;
            $dataEad[] = $countE;
        }

        return [
            'labels' => $labels,
            'stage' => $dataStage,
            'compagnonnage' => $dataCompagnonnage,
            'ead' => $dataEad,
        ];
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
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Affectations',
                        'font' => ['weight' => 'bold']
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre de savoir-faire',
                        'font' => ['weight' => 'bold']
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ]
            ],
        ];
    }
}
