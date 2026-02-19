<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
