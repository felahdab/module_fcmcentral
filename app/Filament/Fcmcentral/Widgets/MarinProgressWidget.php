<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;

class MarinProgressWidget extends Widget
{
    protected static string $view = 'fcmcentral::filament.fcmcentral.widgets.marin-progress-widget';
    
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

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

        $totalActivites = 0;
        $activitesValidees = 0;
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        if (($activite['etat_valid'] ?? false) === true) {
                            $activitesValidees++;
                        }
                    }
                }
            }
        }

        $progress = $totalActivites > 0 ? round(($activitesValidees / $totalActivites) * 100) : 0;

        return [
            'progress' => $progress,
            'completed' => $activitesValidees,
            'total' => $totalActivites
        ];
    }
}
