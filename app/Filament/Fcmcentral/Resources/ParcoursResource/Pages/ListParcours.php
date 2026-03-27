<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;

class ListParcours extends ListRecords
{
    protected static string $resource = ParcoursResource::class;
    protected static ?string $title = 'Parcours';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
