<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource\RelationManagers;
use Modules\FcmCentral\Models\Fonction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource\RelationManagers\CompetencesRelationManager;
use Modules\FcmCentral\Traits\FcmAccessControl;


class FonctionResource extends Resource
{
    use FcmAccessControl;
    
    protected static ?string $model = Fonction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = 'Fonctions';


     /// DROITS ACCES ///    

    public static function canAccess(array $parameters = []): bool
    {
        // Seuls les admins peuvent accéder à cette resource
        return static::canGestionParcours();
    }

    public static function canViewAny(): bool
    {
        return static::canGestionParcours();
    }

    public static function canCreate(): bool
    {
        return static::canGestionParcours();
    }

    public static function canEdit($record): bool
    {
        return static::canGestionParcours();
    }

    public static function canDelete($record): bool
    {
        return static::canGestionParcours();
    }

    public static function canDeleteAny(): bool
    {
        return static::canGestionParcours();
    }

    ///////////////////


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
                TextInput::make('url')
                    ->label('URL')                   
                    ->maxLength(1000),    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('libelle_court','asc')
            ->columns([
               TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->formatStateUsing(function ($state) {
                        return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                    })
                    ->tooltip(fn ($state) => $state)                
                    ->label('URL'),
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
            CompetencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFonctions::route('/'),
            'create' => Pages\CreateFonction::route('/create'),
            'edit' => Pages\EditFonction::route('/{record}/edit'),
        ];
    }
}
