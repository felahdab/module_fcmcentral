<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource;

class ListGels extends ListRecords
{
    protected static string $resource = GelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
