<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource\RelationManagers;
use Modules\FcmCentral\Models\Savoirfaire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Models\Domaine;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource\RelationManagers\ActivitesRelationManager;


class SavoirfaireResource extends Resource
{
    protected static ?string $model = Savoirfaire::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationGroup = 'Parcours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(255),
                TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('Lien Url')
                    ->maxLength(255), 
                TextInput::make('code_sicomp')
                    ->label('Code Sicomp')
                    ->maxLength(255),  
                    Select::make('domaine')
                    ->label('Domaine')
                    ->options(Domaine::all()->pluck('libelle_court','id')),                    
                TextInput::make('coeff')
                    ->label('Coefficient')
                    ->maxLength(255), 
                TextInput::make('duree')
                    ->label('Durée')
                    ->maxLength(255),    
                TextInput::make('an_acquis')
                    ->label('Durée')
                    ->maxLength(255), 
                    TextInput::make('ordre')
                    ->label('Ordre')
                    ->maxLength(255),        
                   Select::make('mod_acquis')
                    ->label('Mod Acquis')
                    ->options([
                        'OUI' => 'Oui',
                        'NON' => 'Non',
                    ]),                         
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
               TextColumn::make('libelle_long')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
               TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            ActivitesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSavoirfaires::route('/'),
            'create' => Pages\CreateSavoirfaire::route('/create'),
            'edit' => Pages\EditSavoirfaire::route('/{record}/edit'),
        ];
    }
}
