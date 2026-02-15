<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
