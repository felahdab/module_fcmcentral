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
use Filament\Tables\Filters\SelectFilter;
use Modules\FcmCentral\Traits\FcmAccessControl;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource\RelationManagers\ActivitesRelationManager;


class SavoirfaireResource extends Resource
{
    
    use FcmAccessControl;
    
    protected static ?string $model = Savoirfaire::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = 'Savoir-faire';

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

    /////////////////

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
                Select::make('domaine_id')
                    ->label('Domaine')
                    ->required()
                    ->searchable()
                    ->options(Domaine::all()->pluck('libelle_court','id')),                    
                TextInput::make('url')
                    ->label('URL')
                    ->maxLength(1000), 
                TextInput::make('code_sicomp')
                    ->label('Code OCTAVE')
                    ->maxLength(255),  
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('libelle_court','asc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('domaine.libelle_court')
                    ->label('Domaine'),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
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
                SelectFilter::make('domaine_id')
                    ->label('Domaine')
                    ->options(Domaine::query()->pluck('libelle_court','id')->toArray()),
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
