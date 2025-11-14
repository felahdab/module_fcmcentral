<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffectation extends EditRecord
{
    protected static string $resource = AffectationResource::class;
    protected static ?string $title = 'Modifier Affectation';    

    protected function getHeaderActions(): array
    {
        return [
           // Actions\DeleteAction::make(),
        ];
    }
}
