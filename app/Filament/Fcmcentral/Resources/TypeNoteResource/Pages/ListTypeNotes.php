<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource;

class ListTypeNotes extends ListRecords
{
    protected static string $resource = TypeNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
