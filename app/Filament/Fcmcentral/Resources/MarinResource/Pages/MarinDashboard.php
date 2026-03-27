<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Models\Marin;

class MarinDashboard extends Page
{
    protected static string $resource = MarinResource::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.marin-dashboard';
    protected static ?string $title = 'Mon Tableau de Bord FCM';

    protected static ?string $navigationLabel = 'Tableau de Bord';

    public static function canAccess(array $parameters = []): bool
    {
        $marin = Marin::fromCurrentUser();

        // Accessible seulement aux marins FCM avec parcours
        return null !== $marin
               && $marin->suivi_en_fcm
               && null !== $marin->complements_fcm
               && null !== $marin->complements_fcm->donnees_du_parcours
               && null !== $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    }

    public function getWidgets(): array
    {
        return [
        ];
    }
}
