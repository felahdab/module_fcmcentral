<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCentral\Services\ParcoursService;

class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ParcoursResource\Widgets\NbVersionsParcoursWidget::class,
        ];
    }

    protected function authorizeUser()
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('figer')
                ->requiresConfirmation()
                ->visible($this->authorizeUser())
                ->form([
                    DatePicker::make('date de debut')
                    ->minDate(Carbon::now()->today()->max(ParcoursService::firstPossibleNewVersionDate($this->getRecord())))
                ])
                ->fillForm(function () {
                    return 
                    [
                    'date de debut' => Carbon::now()->tomorrow()->max(ParcoursService::firstPossibleNewVersionDate($this->getRecord())),
                    ];
                })
                ->action(function (array $data) {
                    $parcours = $this->getRecord();
                    $dto = ParcoursDto::from($parcours);
                    $date_de_debut=new Carbon($data["date de debut"]);
                    $newParcours = ParcoursService::serialize_parcours($dto, $date_de_debut);

                    if ($newParcours == null)
                    {
                        Notification::make()
                            ->title('Le figement du parcours a echoué.')
                            ->warning()
                            ->send();
                        return;
                    }
                    Notification::make()
                        ->title('Le figement du parcours a réussi.')
                        ->success()
                        ->body('Nouvelle version de ce parcours: ' . $newParcours->version)
                        ->send();

                    $event = new UserGeneratedEvent(
                        event_type: "parcours_fige",
                        user_id: auth()->user()->uuid,
                        object_class: get_class($parcours),
                        object_uuid: $parcours->id,
                        detail: [
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
