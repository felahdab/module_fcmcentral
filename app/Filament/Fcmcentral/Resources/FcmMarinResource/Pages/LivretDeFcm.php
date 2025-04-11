<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;

use Filament\Resources\Pages\ViewRecord;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Placeholder;
use Modules\FcmCentral\Http\Livewire\LivretFcm as LivretFcm;



class LivretDeFcm extends ViewRecord
{
    protected static string $resource = FcmMarinResource::class;
  
    public function getRelationManagers(): array
    {
        return [];
    }
    
    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Informations sur le marin')
                ->schema([
                    Grid::make(2)
                    ->schema([
                        Placeholder::make('nom')
                            ->label('Nom Prénom')
                            ->content(fn ($record)=> $record->marin->nom.' '. $record->marin->prenom),
                        
                    ]),
                ]),

                
            Section::make('Livret de FCM')
                ->schema([    
                    //Livewire::make(LivretDeTransformationLivewire::class, ["uuid" => "2bb1800f-c247-434b-abef-4eab4cff0836"]),
                    Livewire::make(LivretFcm::class, ["fcmMarinId" => $this->record->id,"tablePrefix" => 'fcmcentral_']),
                    // Placeholder::make('livret_fcm')
                    // ->content(fn ($record) => view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details-validation',[
                    //     //'parcours' =>json_decode($record?->parcours ?? '{}', true),
                    //     'fcmMarinId' =>$record->id ,
                    // ])),
                ])
            ]);    
    }

    protected function getFooterWidgets(): array
    {
        return [
            //LivretDeTransformationWidget::class
        ];
    }
}
