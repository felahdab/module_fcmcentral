<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;

class CreateMarin extends CreateRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Créer marin';
}
