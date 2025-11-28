<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmPermissionTrait;

class MarinProgressWidget extends Widget
{
    use FcmPermissionTrait;
    
    protected static string $view = 'fcmcentral::filament.fcmcentral.widgets.marin-progress-widget';
    
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
    }

    public static function canView(): bool
    {
        $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
        
        return static::canAccessModuleWithPermissions('widget.marin')
       // && $marin->complements_fcm
        //&& $marin->complements_fcm->donnees_du_parcours
        ;
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

        // ✅ Calculer sur les heures
        $heuresTotal = 0;
        $heuresValidees = 0;
        
        foreach ($parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $heures = 0;
                    if (isset($csf['heures']) && is_numeric($csf['heures'])) {
                        $heures = (float) $csf['heures'];
                    } elseif (isset($csf['jours']) && is_numeric($csf['jours'])) {
                        $heures = (float) $csf['jours'] * 7;
                    }

                    $heuresTotal += $heures;

                    if ($csf['etat_valid'] ?? false) {
                        $heuresValidees += $heures;
                    }
                }
            }
        }

        $progress = $heuresTotal > 0 ? round(($heuresValidees / $heuresTotal) * 100) : 0;

        return [
            'progress' => $progress,
            'completed' => round($heuresValidees, 2),
            'total' => round($heuresTotal, 2)
        ];
    }
}
