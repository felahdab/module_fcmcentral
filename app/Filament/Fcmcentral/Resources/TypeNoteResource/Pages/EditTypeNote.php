<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeNote extends EditRecord
{
    protected static string $resource = TypeNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
