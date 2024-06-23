<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParcours extends ListRecords
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
