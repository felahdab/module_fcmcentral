<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeNotes extends ListRecords
{
    protected static string $resource = TypeNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
