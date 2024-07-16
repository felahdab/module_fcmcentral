<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;

use Modules\FcmCentral\Services\ParcoursService;

use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;


class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('figer')
                ->requiresConfirmation()
                ->form([
                    DatePicker::make('date de debut')
                ])
                ->action(function (array $data) {
                    $parcours = $this->getRecord();
                    $dto = ParcoursDto::from($parcours);
                    $date_de_debut=new Carbon($data["date de debut"]);
                    $newParcours = ParcoursService::serialize_parcours($dto, $date_de_debut);

                    $event = new UserGeneratedEvent(
                        "parcours_fige",
                        auth()->user()->uuid,
                        get_class($parcours),
                        $parcours->id,
                        [
                            "version" => $newParcours->version,
                            "date_debut" => $date_de_debut,
                            "date_fin" => $newParcours->date_fin,
                        ]
                    );

                    event($event);
                }),
        ];
    }
}
