<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Actions\DeleteAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarin extends EditRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Modifier marin';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
