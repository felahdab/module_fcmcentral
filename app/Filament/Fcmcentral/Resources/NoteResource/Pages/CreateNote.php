<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
