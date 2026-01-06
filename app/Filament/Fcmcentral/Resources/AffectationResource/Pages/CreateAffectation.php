<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAffectation extends CreateRecord
{
    protected static string $resource = AffectationResource::class;
    protected static ?string $title = 'Créer Affectation';
}
