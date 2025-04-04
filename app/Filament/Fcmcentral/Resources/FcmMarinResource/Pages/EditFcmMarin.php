<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;

use Modules\RH\Filament\RH\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Widgets\SelectionDeParcours;

class EditFcmMarin extends EditRecord
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
                ->visible(function (Marin $record) {
                    return Arr::get($record->data, "fcm.en_fcm", false);
                }) 
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
