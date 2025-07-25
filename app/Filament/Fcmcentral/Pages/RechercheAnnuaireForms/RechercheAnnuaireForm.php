<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms;

use Filament\Forms;
use Filament\Forms\Get;

use Modules\FcmCommun\Models\Cohorte;

use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Marin;

class RechercheAnnuaireForm 
{
    public static function getSchema()
    {
    return [
        Forms\Components\Wizard\Step::make('FCM')
                ->schema([
                    Forms\Components\Toggle::make('suivre_en_fcm')
                        ->label("Suivre en FCM")
                        ->live(),
                    Forms\Components\Select::make('cohorte_id')
                        ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                        ->label("Cohorte de ce marin")
                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                    Forms\Components\Select::make('mentor_id')
                            ->label("Choisir un mentor")
                            ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                            ->options(
                                Marin::query()
                                    ->where('data->fcm->en_fcm', false)
                                    ->orderBy('nom')
                                    ->get()
                                    ->pluck('fullNameAndGrade', 'uuid')
                                )                              
                            ->searchable(),
                    Forms\Components\Select::make('parcours_id')
                            ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                    ]
                ),     
        ];
    }

}
