<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;

class ListObjectifs extends ListRecords
{
    protected static string $resource = ObjectifResource::class;
    protected static ?string $title = 'Objectifs';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
