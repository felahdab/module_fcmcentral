<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObjectif extends EditRecord
{
    protected static string $resource = ObjectifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
