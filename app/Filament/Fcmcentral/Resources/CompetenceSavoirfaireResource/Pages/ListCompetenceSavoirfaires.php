<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetenceSavoirfaires extends ListRecords
{
    protected static string $resource = CompetenceSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
