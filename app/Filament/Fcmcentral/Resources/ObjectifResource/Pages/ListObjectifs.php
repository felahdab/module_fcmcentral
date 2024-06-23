<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObjectifs extends ListRecords
{
    protected static string $resource = ObjectifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
