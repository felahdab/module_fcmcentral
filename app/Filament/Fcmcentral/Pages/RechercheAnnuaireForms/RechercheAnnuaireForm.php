<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms;

use Illuminate\Support\Facades\DB;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;

use Modules\FcmCommun\Models\Cohorte;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCommun\Models\Marin;

class RechercheAnnuaireForm 
{
    public static function getSchema()
    {
    return [
        Wizard\Step::make('FCM')
                ->schema([
                    Select::make('cohorte')
                        ->label("Cohorte de ce marin")
                        ->options(Cohorte::all()->pluck('libelle_long', 'id')),
                    Select::make('mentor_id')
                        //->options(Marin::pluck('nom','id'))
                        // Recherche dans la base en concat
                        ->options(Marin::select(
                            'rh_marins.id',
                            DB::raw("CONCAT(rh_marins.nom, ' ', rh_marins.prenom,' (', rh_grades.libelle_court, ')') as full_name"))
                            ->leftjoin('rh_grades','rh_marins.grade_id','=','rh_grades.id')
                            ->pluck('full_name','id'))
                        ->searchable(),
                    Select::make('parcoursserialise_id')
                        ->label('Choisir un Parcours')
                        ->options(ParcoursSerialise::pluck('libelle_court', 'id'))
                        ->searchable()
                        ->required()
                    ]
                ),     
        ];
    }

}
