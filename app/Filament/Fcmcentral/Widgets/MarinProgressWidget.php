<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\Widget;
use Modules\FcmCentral\Models\Marin;

class MarinProgressWidget extends Widget
{
    protected static string $view = 'fcmcentral::filament.fcmcentral.widgets.marin-progress-widget';
    
    protected int | string | array $columnSpan = 'full';

    public function getMarin(): ?Marin
    {
        return Marin::fromCurrentUser();
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

        // Calculer le progrès basé sur les activités validées
        $totalActivites = 0;
        $activitesValidees = 0;
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $totalActivites++;
                        if (($activite['etat_validation'] ?? '') === 'valide') {
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

