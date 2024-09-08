<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource;

use Filament\Resources\Pages\ViewRecord;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Livewire;

//use Modules\FcmCommun\Http\Livewire\LivretDeTransformation as LivretDeTransformationLivewire;
//use Modules\FcmCommun\Http\Livewire\Treeview as LivretDeTransformationLivewire;
use Modules\FcmCommun\Http\Livewire\Livret as LivretDeTransformationLivewire;

//use Modules\FcmCommun\Http\Livewire\TestComponent as LivretDeTransformationLivewire;


class LivretDeFcm extends ViewRecord
{
    protected static string $resource = UserResource::class;

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
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('prenom')
                            ->required()
                            ->maxLength(100)
                            ->default(''),
                    ]),
                ]),
            Section::make('Livret de FCM')
                ->schema([    
                    //Livewire::make(LivretDeTransformationLivewire::class, ["uuid" => "2bb1800f-c247-434b-abef-4eab4cff0836"]),
                    Livewire::make(LivretDeTransformationLivewire::class, ["uuid" => $this->record->uuid]),
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
