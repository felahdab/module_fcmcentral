<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;

class EditCompetence extends EditRecord
{
    protected static string $resource = CompetenceResource::class;
    protected static ?string $title = 'Modifier compétence';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
