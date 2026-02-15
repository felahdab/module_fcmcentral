<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;


use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages\ListCompetenceSavoirfaires;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages\CreateCompetenceSavoirfaire;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages\EditCompetenceSavoirfaire;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
 
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource\Pages;

class CompetenceSavoirfaireResource extends Resource
{
    protected static ?string $model = CompetenceSavoirfaire::class;
    protected static string | \UnitEnum | null $navigationGroup = "Catalogue";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('competence_id')
                    ->label('Competence')
                    ->options(Competence::all()->pluck('libelle_court', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('savoirfaire_id')
                    ->label('Savoir-faire')
                    ->options(Savoirfaire::all()->pluck('code', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('ordre')
                    ->label('Ordre')
                    ->required()
                    ->numeric(),

                TextInput::make('heures')
                    ->label('Heures')
                    ->numeric(),

                TextInput::make('jours')
                    ->label('Jours')
                    ->numeric(),

                TextInput::make('libelle_court')
                    ->label('Intitulé')
                    ->maxLength(255),

                Select::make('savoirfaire_type_id')
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
                SelectFilter::make('comeptence_id')
                    ->label('Année')
                    ->options(Competence::all()->pluck('libelle_court', 'id')),
                    SelectFilter::make('parcours_type_id')
                    ->label('Type de parcours')
                    ->options( ParcoursType::all()->pluck('libelle_court', 'id'))
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompetenceSavoirfaires::route('/'),
            'create' => CreateCompetenceSavoirfaire::route('/create'),
            'edit' => EditCompetenceSavoirfaire::route('/{record}/edit'),
        ];
    }
}
