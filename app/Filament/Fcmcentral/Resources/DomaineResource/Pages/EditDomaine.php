<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDomaine extends EditRecord
{
    protected static string $resource = DomaineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
