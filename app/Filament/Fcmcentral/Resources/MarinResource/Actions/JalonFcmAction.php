<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\TypeEtat;
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

                // ✅ SECTION GEL avec toggle
                Section::make('Gestion du Gel')
                    ->description(function ($record) {
                        $gelEnCours = $record->gelActif();
                        if ($gelEnCours) {
                            $debut = $gelEnCours->date_debut->format('d/m/Y');
                            $type = $gelEnCours->typeEtat?->libelle_court ?? '';

                            return "🔴 **Mise en gel**  depuis le {$debut}";
                        }

                        return '🟢 Marin en FCM';
                    })
                    ->schema([
                        // ✅ TOGGLE POUR CRÉER/CLÔTURER GEL
                        Forms\Components\Toggle::make('action_gel')
                            ->label(fn ($record) => $record->gelActif() ? 'Fin de la mise en gel' : 'Mise en gel')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                if ($state) {
                                    $gelActif = $record->gelActif();
                                    if ($gelActif) {
                                        // Mode clôture
                                        $set('date_fin_gel', now()->format('Y-m-d'));
                                    } else {
                                        // Mode création
                                        $set('date_debut_gel', now()->format('Y-m-d'));
                                        $set('type_gel_id', null);
                                        $set('motif_gel', '');
                                    }
                                } else {
                                    // Reset
                                    $set('date_debut_gel', null);
                                    $set('date_fin_gel', null);
                                    $set('type_gel_id', null);
                                    $set('motif_gel', '');
                                }
                            }),

                        // ✅ CRÉATION D'UN NOUVEAU GEL
                        Fieldset::make('Nouveau gel')
                            ->visible(fn (Get $get, $record) => $get('action_gel') && !$record->gelActif())
                            ->schema([
                                // Forms\Components\Select::make('type_gel_id')
                                //     ->label('Type de gel')
                                //     ->options(function () {
                                //         return \Modules\FcmCentral\Models\TypeEtat::pluck('libelle_court', 'id');
                                //     })
                                //     ->searchable()
                                //     ->preload()
                                //     ->required()
                                //     ->createOptionForm([
                                //         Forms\Components\TextInput::make('libelle_court')
                                //             ->required()
                                //             ->label('Libellé')
                                //             ->maxLength(150),
                                //         Forms\Components\TextInput::make('code')
                                //             ->required()
                                //             ->label('Code')
                                //             ->maxLength(50),
                                //     ])
                                //     ->createOptionUsing(fn (array $data) => \Modules\FcmCentral\Models\TypeEtat::create($data)->id),

                                Forms\Components\DatePicker::make('date_debut_gel')
                                    ->label('Date de début de la mise en gel')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Textarea::make('motif_gel')
                                    ->label('Motif de la mise en gel')
                                    ->rows(3),
                            ]),

                        // ✅ CLÔTURE DU GEL EN COURS
                        Fieldset::make('Clôture du gel')
                            ->visible(fn (Get $get, $record) => $get('action_gel') && null !== $record->gelActif())
                            ->schema([
                                Placeholder::make('info_gel_actif')
                                    ->label('Gel actif')
                                    ->content(function ($record) {
                                        $gel = $record->gelActif();
                                        if (!$gel) {
                                            return '';
                                        }

                                        $debut = $gel->date_debut->format('d/m/Y');
                                        $type = $gel->typeEtat?->libelle_court ?? 'Gel';
                                        $motif = $gel->libelle ? " - {$gel->libelle}" : '';

                                        return "**Mise en gel** depuis le {$debut}{$motif}";
                                    }),

                                Forms\Components\DatePicker::make('date_fin_gel')
                                    ->label('Date de fin de la mise en gel')
                                    ->required()
                                    ->default(now()),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->action(function ($record, array $data) {
                // DATE SÉDIMENTATION
                if (($record->complementsFcm?->date_sedimentation ?? null) != ($data['date_sedimentation'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ['date_sedimentation' => $data['date_sedimentation']]
                    ));
                }

                // DATE FCM
                if (($record->complementsFcm?->date_fcm ?? null) != ($data['date_fcm'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ['date_fcm' => $data['date_fcm']]
                    ));
                }

                // DATE COMPÉTENCE
                if (($record->complementsFcm?->date_competence ?? null) != ($data['date_competence'] ?? null)) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ['date_competence' => $data['date_competence']]
                    ));
                }

                // ✅ GESTION DU GEL (seulement si toggle activé)
                if (!empty($data['date_debut_gel'])) {
                    // ddd($record, $data);

                    $typeGel = null;
                    $libelleCourt = null;
                    $uuidEtatType = null;

                    // $typeGel = \Modules\FcmCentral\Models\TypeEtat::find($data['type_gel_id']);
                    $typeGel = TypeEtat::first();

                    $libelleCourt = $typeGel?->libelle_court;
                    $uuidEtatType = $typeGel?->uuid;

                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        [
                            'date_debut_gel' => $data['date_debut_gel'],
                            'type_gel_uuid' => $uuidEtatType ?? null,
                            'type_gel_libelle_court' => $libelleCourt,
                            'motif_gel' => $data['motif_gel'] ?? '',
                        ]
                    ));
                }

                if (!empty($data['date_fin_gel'])) {
                    $gelActif = $record->gelActif();
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        [
                            'uuid_gel' => $gelActif->uuid,
                            'date_fin_gel' => $data['date_fin_gel'],
                        ]
                    ));
                }
            })
        ;
    }
}
