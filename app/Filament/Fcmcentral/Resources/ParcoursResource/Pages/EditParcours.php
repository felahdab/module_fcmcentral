<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCentral\Services\ParcoursService;

class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('figer')
                ->requiresConfirmation()
                ->action(function () {
                    $parcours = $this->getRecord();
                    $dto = ParcoursDto::from($parcours);
                    ParcoursService::serialize_parcours($dto);
                }),
        ];
    }
}
