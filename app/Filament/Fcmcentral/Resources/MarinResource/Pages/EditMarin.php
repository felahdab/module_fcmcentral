<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarin extends EditRecord
{
    protected static string $resource = MarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
