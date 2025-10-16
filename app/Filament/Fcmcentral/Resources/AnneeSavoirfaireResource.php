<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;


use Modules\FcmCentral\Models\AnneeSavoirfaire;
use Modules\FcmCentral\Models\Annee;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AnneeSavoirfaireResource\Pages;

class AnneeSavoirfaireResource extends Resource
{
    use FilamentPermissionTrait;

    protected static ?string $model = AnneeSavoirfaire::class;

    // Permissions marche avec le Trait
    protected static array $permissions = [
        'fcmcentral::gerer_parcours',
        'fcmcentral::droits_admin',
        'fcmcentral::droits_bcf',
        'fcmcentral::droits_supadmin',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('annee_id')
                    ->label('Année')
                    ->options(Annee::all()->pluck('libelle_court', 'id'))
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
                    

                TextColumn::make('annee.libelle_court')
                    ->label('Année')
                    ->badge()
                    ->color(fn($record) => $record->annee->color ?? 'info'),

                TextColumn::make('savoirfaire.code')
                    ->label('Code SF')
                    ->badge()
                    ->color(fn($record) => $record->savoirfaire->color ?? 'info'),

               


                
                TextColumn::make('savoirfaire.libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->savoirfaire->libelle_long,0,40).'...')
                    ,       

                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable(),

                TextColumn::make('heures')
                    ->label('Heures'),

                TextColumn::make('jours')
                    ->label('Jours'),

                TextColumn::make('libelle_court')
                    ->label('Intitulé'),

                TextColumn::make('savoirfaireType.libelle_court')
                    ->label('Type')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('annee_id')
                    ->label('Année')
                    ->options(Annee::all()->pluck('libelle_court', 'id')),
                    Tables\Filters\SelectFilter::make('parcours_type_id')
                    ->label('Type de parcours')
                    ->options(\Modules\FcmCentral\Models\ParcoursType::all()->pluck('libelle_court', 'id'))
                    ->query(function ($query, $state) {
                        if (!$state) {
                            if (empty($state)){
                                // Afichage quand aucun filtre selectionne
                                    return $query;
                            }
                            $query->whereHas('savoirfaire.competence.parcoursType', function ($q) use ($state) {
                                $q->where('id', $state);
                            });
                        }
                    }),
            
            
                

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnneeSavoirfaires::route('/'),
            'create' => Pages\CreateAnneeSavoirfaire::route('/create'),
            'edit' => Pages\EditAnneeSavoirfaire::route('/{record}/edit'),
        ];
    }
}
