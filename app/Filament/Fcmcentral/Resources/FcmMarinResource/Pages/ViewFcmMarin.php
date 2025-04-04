<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFcmMarin extends ViewRecord
{
    protected static string $resource = FcmMarinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
