<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnnee extends EditRecord
{
    protected static string $resource = AnneeResource::class;
    protected static ?string $title = 'Modifier fonction';    

    protected function getHeaderActions(): array
    {
        return [
           // Actions\DeleteAction::make(),
        ];
    }
}
