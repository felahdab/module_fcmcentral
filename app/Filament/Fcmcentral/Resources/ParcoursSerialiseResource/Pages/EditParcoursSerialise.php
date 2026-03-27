<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;

class EditParcoursSerialise extends EditRecord
{
    protected static string $resource = ParcoursSerialiseResource::class;
    protected static ?string $title = 'Consulter PPA';

    protected function getFormActions(): array
    {
        return [];
    }
}
