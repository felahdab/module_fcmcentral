<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParcoursTypes extends ListRecords
{
    protected static string $resource = ParcoursTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
