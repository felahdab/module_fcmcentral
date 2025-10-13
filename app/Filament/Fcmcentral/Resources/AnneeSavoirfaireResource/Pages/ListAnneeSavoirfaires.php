<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnneeSavoirfaires extends ListRecords
{
    protected static string $resource = AnneeSavoirfaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
