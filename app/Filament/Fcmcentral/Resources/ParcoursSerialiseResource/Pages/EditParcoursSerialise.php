<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParcoursSerialise extends EditRecord
{
    protected static string $resource = ParcoursSerialiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
