<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnee extends CreateRecord
{
    protected static string $resource = AnneeResource::class;
    protected static ?string $title = 'Créer fonction';
}
