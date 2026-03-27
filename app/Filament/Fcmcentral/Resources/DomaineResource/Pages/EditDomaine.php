<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;

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
