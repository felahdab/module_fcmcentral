<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class MarinProgressWidget extends Widget
{
    protected string $view = 'fcmcentral::filament.fcmcentral.widgets.marin-progress-widget';

    protected array|int|string $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
    }

    public static function canView(): bool
    {
        $marin = Marin::fromCurrentUser();

        // TODO: faut-il limiter davantage l'accès ? Certains marin disposant
        // d'un parcours doivent-ils être empéchés de voir ce widget ?
        return null != $marin?->complements_fcm?->donnees_du_parcours;
    }

    public function getPdfUrl(): ?string
    {
        return $this->getMarin()
            ?->complements_fcm
            ?->donnees_du_parcours
            ?->parcours_serialise
            ?->parcoursModel
            ?->livret_pdf_url;
    }

    public function getProgressData(): array
    {
        $marin = $this->getMarin();

        if (!$marin || !$marin->complements_fcm) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $donneesParcours = $marin->complements_fcm->donnees_du_parcours;

        if (!$donneesParcours) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $parcours = $donneesParcours->parcoursmarin ?? null;

        if (!$parcours) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        // ✅ Heures pré-calculées par mettreAJourHeures (dans les listeners)
        $heuresTotal = 0;
        $heuresValidees = 0;
        foreach ($parcours['affectations'] ?? [] as $affectation) {
            $heuresTotal += $affectation['heures_total'] ?? 0;
            $heuresValidees += $affectation['heures_validees'] ?? 0;
        }

        // ✅ Même calcul live que le livret → cohérence garantie
        $progress = CalculHeuresParcoursService::calculerPourcentageEtatValid($parcours);

        return [
            'progress' => (int) $progress,
            'completed' => round($heuresValidees, 0),
            'total' => round($heuresTotal, 0),
        ];
    }
}
