<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavoirFaire extends EditRecord
{
    protected static string $resource = SavoirFaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
