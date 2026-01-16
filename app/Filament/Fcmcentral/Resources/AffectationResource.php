<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\RelationManagers;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\RelationManagers\CompetenceSavoirfairesRelationManager;

use Modules\FcmCentral\Models\Affectation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


 

use Modules\FcmCentral\Helpers\OutilsHelper;


class AffectationResource extends Resource
{
    protected static ?string $model = Affectation::class;

    protected static ?string $navigationGroup = "Catalogue";
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(150)
                    ->label('Libellé court'),
                TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(1500)
                    ->label('Libellé long'),
                TextInput::make('code')
                    ->label('code')  
                    ->required()                 
                    ->maxLength(1000),  
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
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn($record)=>$record->color ??   'info') 
                    ,      
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                    TextColumn::make('competences_count')
                    ->counts('competences')
                    ->label('Nb Competence')
                    ->badge()
                    ->color('info'),   
                TextColumn::make('competences.code')
                    ->label('Competences')
                    ->badge()
                    
                    ->color(fn($record)=>
                    $record->competences->first()->color
                    ??  $record->compenteces->first()->competence->color
                    ?? 'info'
                ),    

                // TextColumn::make('savoirfairescodes')
                //     ->label('Savoirs-Faire')
                //     ->formatStateUsing(function ($state, $record) {
                //         return $record->savoirfaires->map(function ($sf) {
                //             $color = $sf->color ?? $sf->competence?->color ?? 'info';
                //             $type = $sf->pivot?->savoirfaireType?->libelle_court ?? '';
                //             return "<span style='color:{$color};'>{$sf->competence?->code}-{$sf->code}" . ($type ? " ({$type})" : "") . "</span>";
                //         })->implode(', ');
                //     })
                //     ->html(),
                    

                // TextColumn::make('libelle_long')
                //     ->searchable()
                //     ->sortable()
                //     ->label('Libellé long')
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('url')
                //     ->formatStateUsing(function ($state) {
                //         return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                //     })
                //     ->tooltip(fn ($state) => $state)                
                //     ->label('URL'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->iconButton()
                ->icon('heroicon-m-eye')
                ->modalHeading('Détail')
                ->extraAttributes([
                    'title' => 'Consulter',
                    'class' => 'btn-consult',
                    ]),
                Tables\Actions\EditAction::make()
                ->iconButton()
                ->icon('heroicon-m-pencil-square')
                ->extraAttributes([
                    'title' => 'Modifier',
                    'class' => 'btn-modif',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CompetenceSavoirfairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffectations::route('/'),
            'create' => Pages\CreateAffectation::route('/create'),
            'edit' => Pages\EditAffectation::route('/{record}/edit'),
        ];
    }
}
