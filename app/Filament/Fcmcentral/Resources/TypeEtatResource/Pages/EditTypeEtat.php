<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeEtatResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeEtatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeEtat extends EditRecord
{
    protected static string $resource = TypeEtatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
