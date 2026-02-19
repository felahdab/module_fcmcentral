<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavoirfaire extends EditRecord
{
    protected static string $resource = SavoirfaireResource::class;
    protected static ?string $title = 'Modifier savoir-faire';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
