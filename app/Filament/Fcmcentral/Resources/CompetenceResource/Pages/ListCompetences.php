<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;

class ListCompetences extends ListRecords
{
    protected static string $resource = CompetenceResource::class;
    protected static ?string $title = 'Compétences';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
