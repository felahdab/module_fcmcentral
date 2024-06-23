<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
