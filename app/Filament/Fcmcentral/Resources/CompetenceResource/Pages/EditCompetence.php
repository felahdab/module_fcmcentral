<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetence extends EditRecord
{
    protected static string $resource = CompetenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
