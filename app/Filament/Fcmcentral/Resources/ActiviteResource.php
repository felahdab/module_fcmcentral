<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource\RelationManagers;
use Modules\FcmCentral\Models\Activite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Modules\FcmCentral\Traits\FcmAccessControl;

class ActiviteResource extends Resource
{
    use FcmAccessControl;
    
    protected static ?string $model = Activite::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = "Stages et tâches";


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

    /////////////


    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('libelle_court')
                ->required()
                ->label('Libellé court')
                ->maxLength(150),
                
            Forms\Components\TextInput::make('libelle_long')
                ->required()
                ->label('Libellé long')
                ->maxLength(1500),
                
            //  Select avec validation stricte
            Forms\Components\Select::make('type_activite')
                ->options(Activite::getTypesActivite())
                ->label('Type')
                ->required()
                ->in(Activite::getValidTypes()) // Validation stricte
                ->default(Activite::TYPE_OBJECTIF),
                
            Forms\Components\TextInput::make('url')
                ->label('URL')
                ->url()
                ->maxLength(1000),
                
            Forms\Components\TextInput::make('duree_validite')
                ->label('Durée de validité (jours)')
                ->numeric()
                ->minValue(0)
                ->suffix('jours'),
                
            Forms\Components\Textarea::make('prerequis')
                ->label('Prérequis')
                ->maxLength(500)
                ->rows(3),

            Forms\Components\TextInput::make('coeff')
                ->label('Coefficient')
                ->numeric()
                ->step(0.1)
                ->minValue(0)
                ->default(1.0),
                
            Forms\Components\TextInput::make('duree')
                ->label('Durée estimée')
                ->helperText('Format libre (ex: 2h, 1 jour, 1 semaine)')
                ->maxLength(50),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('libelle_court','asc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_activite')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                    })
                    ->tooltip(fn ($state) => $state)                
                    ->label('URL'),
                Tables\Columns\TextColumn::make('duree_validite')
                    ->searchable()
                    ->label('Durée de validité (j)'),
                Tables\Columns\TextColumn::make('prerequis')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                    })
                    ->tooltip(fn ($state) => $state)
                    ->label('Prérequis'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                SelectFilter::make('type_activite')
                ->label('Type')
                ->options([
                    'STAGE' => 'Stage',
                    'OBJECTIF' => 'Objectif',
                    'EAD' => 'Ead',
                ]),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivites::route('/'),
            'create' => Pages\CreateActivite::route('/create'),
            'edit' => Pages\EditActivite::route('/{record}/edit'),
        ];
    }
}
