<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarins extends ListRecords
{
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Marins';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('fcmcentral::marin.index') ?? false;
    }
}
