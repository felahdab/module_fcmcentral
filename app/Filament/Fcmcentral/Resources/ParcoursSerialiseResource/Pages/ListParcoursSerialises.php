<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;

class ListParcoursSerialises extends ListRecords
{
    protected static string $resource = ParcoursSerialiseResource::class;
    protected static ?string $title = 'Parcours figés';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
