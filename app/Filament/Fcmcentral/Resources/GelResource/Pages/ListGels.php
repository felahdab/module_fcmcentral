<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
