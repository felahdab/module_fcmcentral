<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnneeSavoirfaire extends EditRecord
{
    protected static string $resource = AnneeSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
