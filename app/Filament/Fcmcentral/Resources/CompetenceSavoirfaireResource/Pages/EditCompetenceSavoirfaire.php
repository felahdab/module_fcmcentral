<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;

class EditCompetenceSavoirfaire extends EditRecord
{
    protected static string $resource = CompetenceSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
