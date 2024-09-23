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
use Modules\FcmCentral\Events\UserGeneratedEvent;
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
        $parcours_service = new ParcoursService();

        return [
            Actions\DeleteAction::make(),
            Action::make('figer')
                ->requiresConfirmation()
                ->visible($this->authorizeUser())
                ->form([
                    DatePicker::make('date de debut')
                    ->minDate(Carbon::now()->today()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord())))
                ])
                ->fillForm(function () use ($parcours_service){
                    return 
                    [
                    'date de debut' => Carbon::now()->tomorrow()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord())),
                    ];
                })
                ->action(function (array $data) use ($parcours_service) {
                    $parcours = $this->getRecord();
                    $dto = ParcoursDto::from($parcours);
                    $date_de_debut=new Carbon($data["date de debut"]);
                    $newParcours = $parcours_service->serialize_parcours($dto, $date_de_debut);

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
                        object_class: "Parcours",
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
