<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\StageResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStages extends ListRecords
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
