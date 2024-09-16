<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Widgets\SelectionDeParcours;

class EditMarin extends EditRecord
{
    protected static string $resource = MarinResource::class;

    protected function getHeaderActions(): array
    {
        $resource = static::getResource();

        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('livret-de-fcm')
                ->label("Livret de FCM")
                ->url($resource::getUrl('livret-de-fcm', ['record' => $this->record]))
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
