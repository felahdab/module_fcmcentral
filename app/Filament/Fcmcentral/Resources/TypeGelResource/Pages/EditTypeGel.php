<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource;

class EditTypeGel extends EditRecord
{
    protected static string $resource = TypeGelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
