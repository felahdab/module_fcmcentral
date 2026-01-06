<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetenceSavoirfaire extends EditRecord
{
    protected static string $resource = CompetenceSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
