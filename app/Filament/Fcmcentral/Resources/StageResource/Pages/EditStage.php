<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\StageResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
