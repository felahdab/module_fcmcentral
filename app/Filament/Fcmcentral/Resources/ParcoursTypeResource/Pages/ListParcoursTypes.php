<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource;

class ListParcoursTypes extends ListRecords
{
    protected static string $resource = ParcoursTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
