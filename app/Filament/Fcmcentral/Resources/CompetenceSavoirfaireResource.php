<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;


use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
 
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

class CompetenceSavoirfaireResource extends Resource
{
    protected static ?string $model = CompetenceSavoirfaire::class;
    protected static ?string $navigationGroup = "Catalogue";
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('competence_id')
                    ->label('Competence')
                    ->options(Competence::all()->pluck('libelle_court', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('savoirfaire_id')
                    ->label('Savoir-faire')
                    ->options(Savoirfaire::all()->pluck('code', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('ordre')
                    ->label('Ordre')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('heures')
                    ->label('Heures')
                    ->numeric(),

                Forms\Components\TextInput::make('jours')
                    ->label('Jours')
                    ->numeric(),

                Forms\Components\TextInput::make('libelle_court')
                    ->label('Intitulé')
                    ->maxLength(255),

                Forms\Components\Select::make('savoirfaire_type_id')
                    ->label('Type de Savoir-faire')
                    ->options(SavoirfaireType::all()->pluck('libelle_court', 'id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre', 'asc')
            ->reorderable('ordre')
            ->defaultPaginationPageOption(25) 
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                    

                TextColumn::make('competence.affectation.libelle_court')
                    ->label('Affectation')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competence->affectaion->color ?? 'info'),
                
                TextColumn::make('competence.code')
                    ->label('Competence')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competence->color ?? 'info'),

                TextColumn::make('savoirfaire.code')
                    ->label('Savoirfaire')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->savoirfaire->color ?? 'info'),

                


                
                TextColumn::make('savoirfaire.libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->savoirfaire->libelle_long,0,40).'...')
                    ->toggleable(isToggledHiddenByDefault:true)
                    , 
                   
                 TextColumn::make('savoirfaire.competence.parcoursType.code')
                    ->label('Parcours')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn($record) => $record->savoirfaire->competence->parcoursType->color ?? 'info'),
                    

                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ,

                TextColumn::make('heures')
                    ->label('Heures'),

                TextColumn::make('jours')
                    ->label('Jours')
                    ->toggleable(isToggledHiddenByDefault:true),

                TextColumn::make('libelle_court')
                    ->label('Intitulé')
                    ->toggleable(isToggledHiddenByDefault:true),

               
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('comeptence_id')
                    ->label('Année')
                    ->options(Competence::all()->pluck('libelle_court', 'id')),
                    Tables\Filters\SelectFilter::make('parcours_type_id')
                    ->label('Type de parcours')
                    ->options( \Modules\FcmCentral\Models\ParcoursType::all()->pluck('libelle_court', 'id'))
                    ->query(function ($query, $state) {
                      
                        if ($state['value'] ===null) {
                            //dd($state);
                             // Afichage quand aucun filtre selectionne
                             return $query;
                           
                        }

                        $query->whereHas('savoirfaire.competence.parcoursType', function ($q) use ($state) {
                            $q->where('id', $state['value']);
                        });
                          
                               
                        
                        
                    }),
            
            
                

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetenceSavoirfaires::route('/'),
            'create' => Pages\CreateCompetenceSavoirfaire::route('/create'),
            'edit' => Pages\EditCompetenceSavoirfaire::route('/{record}/edit'),
        ];
    }
}
