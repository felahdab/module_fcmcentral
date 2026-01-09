<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGel extends EditRecord
{
    protected static string $resource = GelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
