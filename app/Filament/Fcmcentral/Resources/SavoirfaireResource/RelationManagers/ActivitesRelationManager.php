<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Modules\FcmCentral\Models\Activite;


class ActivitesRelationManager extends RelationManager
{
    protected static string $relationship = 'activites';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select ::make('recordId')
                ->label('Activité')
                ->otpions(Activite::all()->pluck('libelle_long','id'))
                ->searchable()
                ->required()
                ->disabled(fn ($context): bool => $context === 'edit'),

               TextInput::make('coeff')
                    ->label('Coefficient')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                TextInput::make('duree')
                    ->label('Durée')
                    ->numeric()
                    ->required(), 
                    
                TextInput::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->required()  ,  

                // Dans le model, enlever protected casts pour afficher en Json
                // Textarea::make('data')
                //     ->label('Données')
                //     ->rows(3),

                KeyValue::make('data')
                ->label('Données JSON')
                ->keyLabel('Clé')
                ->valueLabel('Valeur')
                ->reorderable(),
                    
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_long')
            ->columns([
               TextColumn::make('libelle_long')
               ->label('Activité'),

               TextColumn::make('pivot.coeff')
               ->label('Coefficient')
               ->sortable(),

               TextColumn::make('pivot.duree')
               ->label('Duree')
               ->sortable(),

               TextColumn::make('pivot.ordre')
               ->label('Ordre')
               ->sortable(),

               TextColumn::make('pivot.data')
               ->label('Données')
               ->limit(50)
                ->formatStateUsing(function ($state){
                    if (is_string($state)){
                        $decoded = json_decode($state, true);
                        return $decoded ? collect($decoded)->map(fn($v,$k)=> "$k: $v")->join(', ') : $state;
                    }
                    return is_array($state) ? collect($state)->map(fn($v,$k)=> "$k: $v")->join(', ') : '';
                })
                ->tooltip(function ($state){
                    return is_string($state) ?  $state: json_encode($state, JSON_PRETTY_PRINT);
                })

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    TextInput::make('coeff')
                    ->label('Coefficient')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                TextInput::make('duree')
                    ->label('Durée')
                    ->numeric()
                    ->required(), 
                    
                TextInput::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->required()  ,  

                    KeyValue::make('data')
                    ->label('Données JSON')
                    ->keyLabel('Clé')
                    ->valueLabel('Valeur')
                    ->reorderable(),
                ])
                ,
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->form([
                    TextInput::make('coeff')
                    ->label('Coefficient')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                TextInput::make('duree')
                    ->label('Durée')
                    ->numeric()
                    ->required(), 
                    
                TextInput::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->required()  ,  

                    KeyValue::make('data')
                    ->label('Données JSON')
                    ->keyLabel('Clé')
                    ->valueLabel('Valeur')
                    ->reorderable(),
                ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
