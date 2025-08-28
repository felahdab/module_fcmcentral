<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Filament\Pages\Dashboard;
use Modules\FcmCentral\Models\Marin;

class MarinDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.marin-dashboard';
    
    protected static ?string $title = 'Mon Tableau de Bord FCM';
    
    protected static ?string $navigationLabel = 'Tableau de Bord';

    protected static string $routePath = '/marin-dashboard';

    public static function canAccess(array $parameters = []): bool
    {
        $marin = Marin::fromCurrentUser();
        
        // Accessible seulement aux marins FCM avec parcours
        return $marin !== null && 
               $marin->suivi_en_fcm &&
               $marin->complements_fcm !== null &&
               $marin->complements_fcm->donnees_du_parcours !== null &&
               $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null;
    }

    public function getWidgets(): array
    {
        return [
            \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinProgressWidget::class,
            \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinStatsWidget::class,
        ];
    }
}

