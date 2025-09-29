<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnAcquisResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnAcquisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnAcquis extends ListRecords
{
    protected static string $resource = AnAcquisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
