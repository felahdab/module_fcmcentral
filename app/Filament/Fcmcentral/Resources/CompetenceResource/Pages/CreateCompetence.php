<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;

class CreateCompetence extends CreateRecord
{
    protected static string $resource = CompetenceResource::class;
    protected static ?string $title = 'Créer compétence';
}
