<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
