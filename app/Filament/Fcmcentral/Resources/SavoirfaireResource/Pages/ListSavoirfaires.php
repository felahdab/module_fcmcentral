<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages;

use Filament\Actions\CreateAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSavoirfaires extends ListRecords
{
    protected static string $resource = SavoirfaireResource::class;
    protected static ?string $title = 'Savoir-faire';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
