<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Models\Marin;
use Filament\Resources\Pages\Page;

class MarinDashboard extends Page
{
    protected static string $resource = MarinResource::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.marin-dashboard';
    protected static ?string $title = 'Mon Tableau de Bord FCM';
    
    protected static ?string $navigationLabel = 'Tableau de Bord';

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
            
        ];
    }

}
