<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;

class CreateObjectif extends CreateRecord
{
    protected static string $resource = ObjectifResource::class;
    protected static ?string $title = 'Créer Objectif';
}
