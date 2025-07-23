<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Carbon\Carbon;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\DatePicker;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Models\ParcoursSerialise;

class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;
    protected static ?string $title = 'Modifier parcours';

    protected function getHeaderWidgets(): array
    {
        return [
            // ParcoursResource\Widgets\NbVersionsParcoursWidget::class,
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
                ->modalHeading(fn($record) => 'Figer le Parcours : '.$record->libelle_court)
                ->modalDescription('En figeant le parcours, à compter de la date saisie, tous les parcours individualisés des marins suivant ce PPA seront mis à jour. Cette action est irreversible.')
                ->form([
                    TextInput::make('version')
                        ->label('Version')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                    DatePicker::make('date de debut')
                        ->minDate(Carbon::now()->today()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord())))
                ])
                ->fillForm(function ($record) use ($parcours_service){
                    return 
                    [
                        'date de debut' => Carbon::now()->tomorrow()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord())),
                        'version' => $record->getLatestSerializedVersion() + 1,
                    ];
                })
                ->action(function (array $data, $record) use ($parcours_service) {

                    $event = new UserGeneratedEvent(
                        UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
                        auth()->user()->uuid,
                        ObjectClasses::PARCOURS,
                        $record->uuid,
                        [
                            "date_de_debut" => $data["date de debut"], 
                            "version"=> $data["version"], 
                        ]
                    );

                    event($event);
                }),
        ];
    }
}
