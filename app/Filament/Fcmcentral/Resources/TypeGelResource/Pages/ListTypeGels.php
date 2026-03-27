<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource;

class ListTypeGels extends ListRecords
{
    protected static string $resource = TypeGelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
