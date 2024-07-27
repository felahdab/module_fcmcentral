<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Widgets\SelectionDeParcours;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getWidgets()
    {
        return [
            SelectionDeParcours::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SelectionDeParcours::class,
        ];
    }
}
