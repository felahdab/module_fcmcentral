<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ModAcquisResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ModAcquisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModAcquis extends EditRecord
{
    protected static string $resource = ModAcquisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
