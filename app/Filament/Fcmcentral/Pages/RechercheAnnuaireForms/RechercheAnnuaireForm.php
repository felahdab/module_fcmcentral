<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms;

use Illuminate\Support\Facades\DB;

use Filament\Forms;
use Filament\Forms\Get;

use Modules\FcmCommun\Models\Cohorte;

use Modules\FcmCentral\Models\ParcoursSerialise;
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
                        ->options(Cohorte::all()->pluck('libelle_long', 'id')),
                    Forms\Components\Select::make('mentor_id')
                            ->label("Choisir un mentor")
                            ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                            ->options(Marin::all()->pluck('fullNameAndGrade','id'))
                            ->searchable(),
                    Forms\Components\Select::make('parcoursserialise_id')
                        ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                        ->label('Choisir un Parcours')
                        ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                        ->searchable()
                    ]
                ),     
        ];
    }

}
