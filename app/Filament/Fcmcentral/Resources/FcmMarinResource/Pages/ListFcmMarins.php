<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFcmMarins extends ListRecords
{
    protected static string $resource = FcmMarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
