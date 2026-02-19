<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

use Filament\Forms;
use Filament\Actions\Action;


use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;

class ToggleFcmAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->modalHeading(fn ($record) => $record->suivi_en_fcm ? 'Marin en FCM' : 'Activer FCM')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->requiresConfirmation()
            ->schema(function ($record) {
                // Si admin et déjà en FCM, pas de modal, action directe
                if (
                    auth()->user()->can('fcmcentral::fcm.admin') &&
                    $record->suivi_en_fcm &&
                    $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN'
                ) {
                    return [];
                }
                // Sinon, modal classique
                return [
                    Forms\Components\Select::make('cohorte_id')
                        ->label("Choisir la cohorte")
                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.cohorte'))
                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                    Forms\Components\Select::make('mentor_id')
                        ->label("Choisir un mentor")
                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.mentor'))
                        ->options(
                            Marin::query()
                                ->whereNull('deleted_at')
                                ->orderBy('nom')
                                ->orderBy('prenom')
                                ->get()
                                ->mapWithKeys(function ($marin) {
                                    $fullName = trim($marin->prenom . ' ' . $marin->nom);
                                    $grade = $marin->grade?->libelle_court ?? '';
                                    $label = $grade ? $grade . ' ' . $fullName : $fullName;
                                    return [$marin->uuid => $label];
                                })
                        )
                        ->searchable(),
                    Forms\Components\Select::make('parcours_id')
                        ->label('Choisir un Parcours')
                        ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                        ->searchable()
                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.parcours'))
                ];
            })
            ->action(function ($record, $data) {
                // Si admin et déjà en FCM, on désactive FCM sans modal
                if (
                    auth()->user()->can('fcmcentral::fcm.admin') &&
                    $record->estEnFcm() &&
                    $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN'
                ) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::MARIN_PAS_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        []
                    ));
                    \Filament\Notifications\Notification::make()
                        ->title('FCM désactivé')
                        ->success()
                        ->send();
                    return;
                }
                
                // Activation FCM + historisation état FCM_sed
                event(new UserGeneratedEvent(
                    UserGeneratedEventTypes::MARIN_EN_FCM,
                    auth()->user()->uuid,
                    ObjectClasses::MARIN,
                    $record->uuid,
                    ['statut_fcm' => 'En validation']
                ));
                // Attribution cohorte/mentor/parcours
                if (!empty($data['cohorte_id'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ["cohorte" => $data["cohorte_id"]]
                    ));
                }
                if (!empty($data['mentor_id'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ["mentor" => $data["mentor_id"]]
                    ));
                }
                if (!empty($data['parcours_id'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ["parcours" => $data["parcours_id"]]
                    ));
                }
            });

    }

}