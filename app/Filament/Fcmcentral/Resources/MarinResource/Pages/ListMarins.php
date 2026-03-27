<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;

class ListMarins extends ListRecords
{
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Marins';

    // Methode pour reperer si le filtre est actif pour enlver la colonne FCM
    public function isFilterEnFcmActive(): bool
    {
        return ($this->tableFilters['en_fcm']['value'] ?? null) === 'enFcm';
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('fcmcentral::marin.index') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
