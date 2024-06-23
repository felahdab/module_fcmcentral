<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFonction extends EditRecord
{
    protected static string $resource = FonctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
