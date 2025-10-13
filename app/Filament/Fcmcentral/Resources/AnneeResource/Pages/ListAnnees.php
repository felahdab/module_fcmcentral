<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnnees extends ListRecords
{
    protected static string $resource = AnneeResource::class;
    protected static ?string $title = 'Annees'; 

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
