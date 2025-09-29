<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ModAcquisResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ModAcquisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModAcquis extends ListRecords
{
    protected static string $resource = ModAcquisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
