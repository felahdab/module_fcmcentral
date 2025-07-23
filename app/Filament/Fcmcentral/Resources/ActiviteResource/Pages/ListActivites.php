<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivites extends ListRecords
{
    protected static string $resource = ActiviteResource::class;
    protected static ?string $title = 'Stages et tâches';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
