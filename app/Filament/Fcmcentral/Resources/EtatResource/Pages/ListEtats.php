<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEtats extends ListRecords
{
    protected static string $resource = EtatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
