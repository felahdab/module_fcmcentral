<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivite extends EditRecord
{
    protected static string $resource = ActiviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
