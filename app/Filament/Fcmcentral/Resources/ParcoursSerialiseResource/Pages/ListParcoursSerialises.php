<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
