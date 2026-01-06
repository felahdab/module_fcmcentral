<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDomaines extends ListRecords
{
    protected static string $resource = DomaineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
