<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;

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
