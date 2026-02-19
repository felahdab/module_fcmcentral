<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffectations extends ListRecords
{
    protected static string $resource = AffectationResource::class;
    protected static ?string $title = 'Affectations'; 

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
