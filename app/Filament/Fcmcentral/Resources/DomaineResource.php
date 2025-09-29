<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\RelationManagers;
use Modules\FcmCentral\Models\Domaine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\RelationManagers\SavoirfaireRelationManager;
use Modules\FcmCentral\Traits\FcmAccessControl;

class DomaineResource extends Resource
{
    use FcmAccessControl;
   
    protected static ?string $model = Domaine::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = 'Domaines';

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
                TextColumn::make('savoirfaires_count')
                    ->counts('savoirfaires')
                    ->label('Nb Savoir-faire')
                    ->badge()
                    ->color('info'),
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
            SavoirfaireRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDomaines::route('/'),
            'create' => Pages\CreateDomaine::route('/create'),
            'edit' => Pages\EditDomaine::route('/{record}/edit'),
        ];
    }
}
