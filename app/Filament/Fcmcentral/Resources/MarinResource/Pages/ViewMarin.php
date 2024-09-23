<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMarin extends ViewRecord
{
    protected static string $resource = MarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
