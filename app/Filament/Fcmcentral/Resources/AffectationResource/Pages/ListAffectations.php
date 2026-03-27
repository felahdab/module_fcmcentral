<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;

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
