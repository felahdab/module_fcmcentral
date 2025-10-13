<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinProgressWidget extends Widget
{
    use FcmAccessControl;
   
    protected static string $view = 'fcmcentral::filament.fcmcentral.widgets.marin-progress-widget';
   
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
    }

    public static function canView():bool
    {
       return static::canAccessMarinLivret();
    }

    public function getProgressData(): array
    {
        $marin = $this->getMarin();
       
        if (!$marin || !$marin->complements_fcm) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
       
        if (!$parcours) {
            return ['progress' => 0, 'completed' => 0, 'total' => 0];
        }

        // ✅ Calculer sur les savoir-faires au lieu des activités
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
            'completed' => $savoirfairesValides,
            'total' => $totalSavoirfaires
        ];
    }
}

