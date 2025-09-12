<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms;

use Filament\Forms;
use Filament\Forms\Get;

use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Traits\FcmAccessControl;

class RechercheAnnuaireForm 
{
    use FcmAccessControl;

    public static function getSchema()
    {
    return [
        Forms\Components\Wizard\Step::make('FCM')
                ->visible(fn() => auth()->user()?->can('fcmcentral::suivre_en_fcm'))
                ->schema([
                    Forms\Components\Toggle::make('suivre_en_fcm')
                        ->visible(fn() => auth()->user()?->can('fcmcentral::suivre_en_fcm'))
                        ->label("Suivre en FCM")
                        ->live(),
                    
                    Forms\Components\Select::make('cohorte_id')
                        ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::attribuer_cohorte'))
                        ->label("Cohorte de ce marin")
                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                    
                    // Champ mentor visible seulement pour les admins
                    Forms\Components\Select::make('mentor_id')
                            ->label("Choisir un mentor")
                            ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::attribuer_mentor'))
                            ->options(
                                Marin::query()
                                    ->where('data->fcm->en_fcm', false)
                                    ->orderBy('nom')
                                    ->get()
                                    ->pluck('fullNameAndGrade', 'uuid')
                                )                              
                            ->searchable(),
                    Forms\Components\Select::make('parcours_id')
                            ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::suivre_en_fcm'))
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                    ]
                ),     
        ];
    }
}

