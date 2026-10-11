<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\TypeEtat;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class GelAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->modalHeading('Gestion du Gel FCM')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->modalWidth('xl')
            ->schema([
                Forms\Components\Toggle::make('action_gel')
                    ->label(fn ($record) => $record->gelActif() ? 'Lever le gel' : 'Mettre en gel')
                    ->live()
                    ->default(false)
                    ->afterStateUpdated(function ($state, Set $set, $record) {
                        if ($state) {
                            if ($record->gelActif()) {
                                $set('date_fin_gel', now()->format('Y-m-d'));
                            } else {
                                $set('date_debut_gel', now()->format('Y-m-d'));
                                $set('motif_gel', '');
                            }
                        } else {
                            $set('date_debut_gel', null);
                            $set('date_fin_gel', null);
                            $set('motif_gel', '');
                        }
                    }),

                Fieldset::make('Mise en gel')
                    ->visible(fn (Get $get, $record) => $get('action_gel') && !$record->gelActif())
                    ->schema([
                        Forms\Components\DatePicker::make('date_debut_gel')
                            ->label('Date de début')
                            ->required()
                            ->default(now()),

                        Forms\Components\Textarea::make('motif_gel')
                            ->label('Motif')
                            ->rows(3),
                    ]),

                Fieldset::make('Lever le gel')
                    ->visible(fn (Get $get, $record) => $get('action_gel') && null !== $record->gelActif())
                    ->schema([
                        Forms\Components\Placeholder::make('info_gel_actif')
                            ->label('Gel en cours')
                            ->content(function ($record) {
                                $gel = $record->gelActif();
                                if (!$gel) {
                                    return '';
                                }
                                $debut = $gel->date_debut->format('d/m/Y');
                                $motif = $gel->libelle ? " — {$gel->libelle}" : '';

                                return "En gel depuis le {$debut}{$motif}";
                            }),

                        Forms\Components\DatePicker::make('date_fin_gel')
                            ->label('Date de fin')
                            ->required()
                            ->default(now()),
                    ]),
            ])
            ->action(function ($record, array $data) {
                if (!empty($data['date_debut_gel'])) {
                    $typeGel = TypeEtat::first();

                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->nid,
                        [
                            'date_debut_gel'          => $data['date_debut_gel'],
                            'type_gel_uuid'           => $typeGel?->uuid,
                            'type_gel_libelle_court'  => $typeGel?->libelle_court,
                            'motif_gel'               => $data['motif_gel'] ?? '',
                        ]
                    ));
                }

                if (!empty($data['date_fin_gel'])) {
                    $gelActif = $record->gelActif();

                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->nid,
                        [
                            'uuid_gel'     => $gelActif->uuid,
                            'date_fin_gel' => $data['date_fin_gel'],
                        ]
                    ));
                }
            });
    }
}
