<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Modules\FcmCentral\Models\ParcoursType;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages\ListSavoirfaires;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages\CreateSavoirfaire;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages\EditSavoirfaire;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages;
use Modules\FcmCentral\Models\Savoirfaire;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Models\Domaine;
use Modules\FcmCentral\Models\Competence;
use Filament\Tables\Filters\SelectFilter;


use Modules\FcmCentral\Helpers\OutilsHelper;
 

class SavoirfaireResource extends Resource
{
    protected static ?string $model = Savoirfaire::class;

    protected static string | \UnitEnum | null $navigationGroup = "Catalogue";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
                TextInput::make('code')
                    ->required()
                    ->label('Code')
                    ->maxLength(150),        
                Select::make('domaine_id')
                    ->label('Domaine')
                    ->required()
                    ->searchable()
                    ->options(Domaine::all()->pluck('libelle_court','id')), 
                Select::make('competence_id')
                    ->label('Competence')
                    ->required()
                    ->searchable()
                    ->options(Competence::all()->pluck('libelle_court','id')),                       
                // TextInput::make('url')
                //     ->label('URL')
                //     ->maxLength(1000), 
                TextInput::make('same')
                    ->label('same')
                    ->maxLength(255), 
                TextInput::make('code_sicomp')
                    ->label('Code OCTAVE')
                    ->maxLength(255), 
                Select::make('color')
                    ->options(OutilsHelper::selectColors())
                    ->label('Couleur du Badge'),             
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre','asc')
            ->reorderable('ordre') 
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                    TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn($record)=>$record->color ??  $record->competence->color ?? 'info') 
                    ,  
                   
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long,0,40).'...')
                    ,   
                    TextColumn::make('domaine.libelle_court')
                    ->label('Domaine')
                    ->badge()
                    //->color('danger') 
                    ->color(fn($record)=>$record->domaine->color ?? 'info')
                    ,     
                    TextColumn::make('competence.code')
                    ->label('Competence')
                    ->badge()
                    ->color(fn($record)=>$record->competence->color ?? 'info')
                    ,
                TextColumn::make('competence.parcoursType.libelle_court')
                    ->label('Type Parcours')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record)=>$record->competence->parcoursType->color ?? 'info')
                    ,
                TextColumn::make('ordre')

            ])
            ->filters([
                //
                SelectFilter::make('domaine_id')
                    ->label('Domaine')
                    ->options(Domaine::query()->pluck('libelle_court','id')->toArray()),

                   SelectFilter::make('parcours_type_id')
                    ->label('Type de parcours')
                    ->options( ParcoursType::all()->pluck('libelle_court', 'id'))
                    ->query(function ($query, $state) {
                      
                        if ($state['value'] ===null) {
                            //dd($state);
                             // Afichage quand aucun filtre selectionne
                             return $query;
                           
                        }

                        $query->whereHas('competence.parcoursType', function ($q) use ($state) {
                            $q->where('id', $state['value']);
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                ->iconButton()
                ->icon('heroicon-m-eye')
                ->modalHeading('Détail')
                ->extraAttributes([
                    'title' => 'Consulter',
                    'class' => 'btn-consult',
                    ]),
                EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-pencil-square')
                ->extraAttributes([
                    'title' => 'Modifier',
                    'class' => 'btn-modif',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSavoirfaires::route('/'),
            'create' => CreateSavoirfaire::route('/create'),
            'edit' => EditSavoirfaire::route('/{record}/edit'),
        ];
    }
}
