<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetences extends ListRecords
{
    protected static string $resource = CompetenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
