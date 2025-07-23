<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Modules\FcmCentral\Models\Activite;


class ActivitesRelationManager extends RelationManager
{
    protected static string $relationship = 'activites';
    protected static ?string $title = 'Stage(s) ou tâche(s) attachés';

//     protected function processPivotData(array $data): array
// {
//     // Traitement de la colonne data JSON
//     if (isset($data['data'])) {
//         if (is_array($data['data'])) {
//             // Nettoyer les valeurs vides
//             $cleanData = array_filter($data['data'], function($value, $key) {
//                 return $value !== null && $value !== '' && trim($key) !== '';
//             }, ARRAY_FILTER_USE_BOTH);
            
//             $data['data'] = !empty($cleanData) ? json_encode($cleanData) : null;
//         } elseif (is_string($data['data']) && trim($data['data']) === '') {
//             $data['data'] = null;
//         }
//     } else {
//         $data['data'] = null;
//     }
 
//     // Assurer les bons types
//     $data['coeff'] = (float) ($data['coeff'] ?? 1.0);
//     $data['duree'] = (string) ($data['duree'] ?? '1');
//     $data['ordre'] = (int) ($data['ordre'] ?? 1);
 
//     return $data;
// }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select ::make('recordId')
                    ->label('Tâche ou stage')
                    ->options(Activite::all()->pluck('libelle_court','id'))
                    ->searchable()
                    ->required()
                    ->disabled(fn ($context): bool => $context === 'edit'),

               TextInput::make('coeff')
                    ->label('Coefficient')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                TextInput::make('duree')
                    ->label('Durée(h)')
                    ->numeric()
                    ->required(), 
                    
                TextInput::make('ordre')
                    ->label('Ordre affichage')
                    ->numeric()
                    ->required()  ,  

                // Dans le model, enlever protected casts pour afficher en Json
                // Textarea::make('data')
                //     ->label('Données')
                //     ->rows(3),

                KeyValue::make('data')
                    ->label('Données JSON form')
                    ->keyLabel('Clé')
                    ->valueLabel('Valeur')
                    ->reorderable()
                    ->visible(auth()->user()->IsSuperAdmin()),
                    
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->columns([
                TextColumn::make('libelle_court')
                ->label('Libellé court')
                ->sortable(),
                
                TextColumn::make('type_activite')
                ->label('Type'),

                TextColumn::make('pivot.coeff')
                ->label('Coefficient'),

                TextColumn::make('pivot.duree')
                ->label('Durée(h)'),

                TextColumn::make('pivot.ordre')
                ->label('Ordre'),

                TextColumn::make('pivot.data')
                ->label('Données')
                ->limit(20)
                ->visible(auth()->user()->IsSuperAdmin())               
                ->tooltip(function ($state){
                    return is_string($state) ?  $state: json_encode($state, JSON_PRETTY_PRINT);
                })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->modalHeading('Attacher stage ou tâche')
                ->color('primary')
                ->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    TextInput::make('coeff')
                        ->label('Coefficient')
                        ->numeric()
                        ->step(0.01)
                        ->required(),

                    TextInput::make('duree')
                        ->label('Durée(h)')
                        ->numeric()
                        ->required(), 
                        
                    TextInput::make('ordre')
                        ->label('Ordre')
                        ->numeric()
                        ->required()  ,  

                    KeyValue::make('data')
                        ->label('Données')
                        ->keyLabel('Clé')
                        ->valueLabel('Valeur')
                        ->reorderable()
                        ->visible(auth()->user()->IsSuperAdmin())
                        
                                    ,
                ])               
        ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-pencil-square')
                ->extraAttributes([
                    'title' => 'Modifier',
                    'class' => 'btn-modif',
                    ])
                ->form([
                    TextInput::make('coeff')
                        ->label('Coefficient')
                        ->numeric()
                        ->step(0.01)
                        ->required(),

                    TextInput::make('duree')
                        ->label('Durée(h)')
                        ->numeric()
                        ->required(), 
                        
                    TextInput::make('ordre')
                        ->label('Ordre')
                        ->numeric()
                        ->required()  ,  

                    KeyValue::make('data')
                        ->label('Données')
                        ->keyLabel('Clé')
                        ->valueLabel('Valeur')
                        ->reorderable(),
                ]),
                Tables\Actions\DetachAction::make()
                ->iconButton()
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->extraAttributes([
                    'title' => 'Détacher',
                    ]),
            ])
            ->bulkActions([                                                                                                                                                                                                                                                                                                                                                                                                           
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
