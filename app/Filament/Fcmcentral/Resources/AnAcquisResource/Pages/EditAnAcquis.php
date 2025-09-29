<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnAcquisResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnAcquisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnAcquis extends EditRecord
{
    protected static string $resource = AnAcquisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
