<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms;

use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms;

use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Marin;


class RechercheAnnuaireForm 
{
   

    public static function getSchema()
    {
    return [
        Step::make('FCM')
                ->visible(fn() => auth()->user()?->can('fcmcentral::marin.suivreEnFcm'))
                ->schema([
                    Toggle::make('suivre_en_fcm')
                        ->visible(fn() => auth()->user()?->can('fcmcentral::marin.suivreEnFcm'))
                        ->label("Suivre en FCM")
                        ->live(),
                    
                    Select::make('cohorte_id')
                        ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::attribuer.cohorte'))
                        ->label("Cohorte de ce marin")
                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                    
                    // Champ mentor visible seulement pour les admins
                    Select::make('mentor_id')
                    ->label("Choisir un mentor")
                    ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::attribuer.mentor'))
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
                    ->searchable()

                    ->placeholder('Sélectionnez un mentor')
                    ->helperText('Seuls les marins non en FCM peuvent être mentors')
                    ->preload() // Charge les options au chargement de la page
                    ->native(false), // Utilise le select stylé Filament
                
                    Select::make('parcours_id')
                            ->visible(fn(Get $get) => $get('suivre_en_fcm') && auth()->user()?->can('fcmcentral::attribuer.parcours'))
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                    ]
                ),     
        ];
    }
}

