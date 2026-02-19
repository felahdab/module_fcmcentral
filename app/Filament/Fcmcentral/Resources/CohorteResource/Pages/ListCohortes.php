<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCohortes extends ListRecords
{
    protected static string $resource = CohorteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
