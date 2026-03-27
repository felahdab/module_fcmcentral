<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;

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
