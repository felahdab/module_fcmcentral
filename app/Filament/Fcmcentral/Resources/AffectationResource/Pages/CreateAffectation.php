<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;

class CreateAffectation extends CreateRecord
{
    protected static string $resource = AffectationResource::class;
    protected static ?string $title = 'Créer Affectation';
}
