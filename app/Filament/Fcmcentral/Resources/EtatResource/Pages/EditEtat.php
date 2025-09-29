<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEtat extends EditRecord
{
    protected static string $resource = EtatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
