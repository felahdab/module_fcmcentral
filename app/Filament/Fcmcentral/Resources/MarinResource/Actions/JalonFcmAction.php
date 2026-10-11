<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class JalonFcmAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->modalHeading('Modifier les Jalons FCM')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->modalWidth('3xl')
            ->schema([
                // ✅ SECTION JALONS FCM avec toggles
                Section::make('Jalons FCM')
                    ->schema([
                        // DATE SÉDIMENTATION

                        Forms\Components\DatePicker::make('date_sedimentation')
                            ->label('Date de début d\'entrée en FCM')

                            ->default(fn ($record) => $record->complementsFcm?->date_sedimentation)
                            ->required(),

                        // DATE FCM
                        Forms\Components\DatePicker::make('date_fcm')
                            ->label('Date de fin de sédimentation')
                            ->default(fn ($record) => $record->complementsFcm?->date_fcm),

                        Forms\Components\Checkbox::make('date_fcm_non_renseignee')
                            ->label('Date non renseignée')
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $set('date_fcm', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('date_competence')
                            ->label('Date de vert compétence')
                            ->default(fn ($record) => $record->complementsFcm?->date_competence),

                        Forms\Components\Checkbox::make('date_competence_non_renseignee')
                            ->label('Date non renseignée')
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $set('date_competence', null);
                                }
                            }),
                    ])
                    ->columns(1),

            ])
            ->action(function ($record, array $data) {
                // DATE SÉDIMENTATION
                if (($record->complementsFcm?->date_sedimentation ?? null) != ($data['date_sedimentation'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->nid,
                        ['date_sedimentation' => $data['date_sedimentation']]
                    ));
                }

                // DATE FCM
                if (($record->complementsFcm?->date_fcm ?? null) != ($data['date_fcm'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->nid,
                        ['date_fcm' => $data['date_fcm']]
                    ));
                }

                // DATE COMPÉTENCE
                if (($record->complementsFcm?->date_competence ?? null) != ($data['date_competence'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->nid,
                        ['date_competence' => $data['date_competence']]
                    ));
                }

            })
        ;
    }
}
