<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
