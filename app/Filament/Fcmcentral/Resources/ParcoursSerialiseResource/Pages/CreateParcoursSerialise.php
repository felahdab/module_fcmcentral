<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;

class CreateParcoursSerialise extends CreateRecord
{
    protected static string $resource = ParcoursSerialiseResource::class;
    protected static ?string $title = 'Figer parcours';
}
