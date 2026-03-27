<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;

class ListCompetenceSavoirfaires extends ListRecords
{
    protected static string $resource = CompetenceSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
