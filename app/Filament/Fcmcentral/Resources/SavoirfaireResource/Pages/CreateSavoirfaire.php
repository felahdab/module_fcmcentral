<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource;

class CreateSavoirfaire extends CreateRecord
{
    protected static string $resource = SavoirfaireResource::class;
    protected static ?string $title = 'Créer savoir-faire';
}
