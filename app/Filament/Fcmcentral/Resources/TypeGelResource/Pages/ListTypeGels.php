<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeGels extends ListRecords
{
    protected static string $resource = TypeGelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
