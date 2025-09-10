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
                ->schema([
                    Forms\Components\Toggle::make('suivre_en_fcm')
                        ->label("Suivre en FCM")
                        ->live(),
                    
                    Forms\Components\Select::make('cohorte_id')
                        ->visible(fn(Get $get) => $get('suivre_en_fcm'))
                        ->label("Cohorte de ce marin")
                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                    
                    // Champ mentor visible seulement pour les admins
                    Forms\Components\Select::make('mentor_id')
                        ->label("Choisir un mentor")
                        ->visible(function(Get $get) {
                            return $get('suivre_en_fcm') && static::canAccessAdminMenus();
                        })
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
                    
                    // Message informatif pour les mentors
                    Forms\Components\Placeholder::make('mentor_info')
                        ->visible(function(Get $get) {
                            return $get('suivre_en_fcm') && static::canAccessMentorFeatures() && !static::canAccessAdminMenus();
                        })
                        ->label('Mentor assigné')
                        ->content(function() {
                            $currentMarin = Marin::fromCurrentUser();
                            if ($currentMarin) {
                                $grade = $currentMarin->grade?->libelle_court ?? '';
                                $fullName = trim($currentMarin->prenom . ' ' . $currentMarin->nom);
                                return $grade ? $grade . ' ' . $fullName : $fullName;
                            }
                            return 'Vous serez automatiquement assigné comme mentor';
                        })
                        ->extraAttributes(['class' => 'text-success-600 font-medium']),
                    
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

