<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin;

use Filament\Widgets\ChartWidget;
use Modules\FcmCentral\Models\Marin;
use Carbon\Carbon;

class MarinEvolutionWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution des heures validées';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        //$marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
        // TODO: faut-il limiter davantage l'accès ? Certains marin disposant
        // d'un parcours doivent-ils être empéchés de voir ce widget ?
        //return $marin?->complements_fcm?->donnees_du_parcours != null;
        return false;
    }

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
    }

    protected function getData(): array
    {
        $marin = $this->getMarin();
        
        if (!$marin || !$marin->complements_fcm) {
            return ['datasets' => [], 'labels' => []];
        }

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
        
        if (!$parcours) {
            return ['datasets' => [], 'labels' => []];
        }

        // ✅ Collecter les heures par mois
        $monthly = [];

        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if (empty($csf['date_validation'])) continue;
                    if (!($csf['etat_valid'] ?? false)) continue;

                    $date = Carbon::parse($csf['date_validation'])->startOfMonth();
                    $key = $date->format('Y-m');

                    $heures = 0;
                    if (isset($csf['heures']) && is_numeric($csf['heures'])) {
                        $heures = (float) $csf['heures'];
                    } elseif (isset($csf['jours']) && is_numeric($csf['jours'])) {
                        $heures = (float) $csf['jours'] * 7;
                    }

                    $monthly[$key] = ($monthly[$key] ?? 0) + $heures;
                }
            }
        }

        if (empty($monthly)) {
            return ['datasets' => [], 'labels' => []];
        }

        ksort($monthly);

        // ✅ Cumuler les heures
        $labels = [];
        $data = [];
        $cumul = 0;

        foreach ($monthly as $key => $heures) {
            $cumul += $heures;
            $labels[] = Carbon::createFromFormat('Y-m', $key)->translatedFormat('M Y');
            $data[] = round($cumul, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Heures validées (cumul)',
                    'data' => $data,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + context.parsed.y + "h"; }'
                    ]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Heures cumulées',
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Mois',
                    ]
                ]
            ],
        ];
    }
}
