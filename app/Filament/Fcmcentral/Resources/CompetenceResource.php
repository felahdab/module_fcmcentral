<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers;
use Modules\FcmCentral\Models\Competence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Modules\FcmCentral\Traits\FcmAccessControl;



use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers\SavoirfairesRelationManager;

class CompetenceResource extends Resource
{
    use FcmAccessControl;
    
    protected static ?string $model = Competence::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 13;

    protected static ?string $navigationGroup = 'Parcours';
    protected static ?string $navigationLabel = 'Compétences';


    public static function canAccess(array $parameters = []): bool
    {
        // Seuls les admins peuvent accéder à cette resource
        return static::canAccessAdminMenus();
    }

    public static function canViewAny(): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canCreate(): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canEdit($record): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canDelete($record): bool
    {
        return static::canAccessAdminMenus();
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccessAdminMenus();
    }

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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->sortable()
                    ->searchable(),   
                TextColumn::make('libelle_long')
                    ->label('Libellé long') 
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return strlen($state) > 20 ? substr($state, 0, 20) . '...' : $state;
                    })
                    ->tooltip(fn ($state) => $state)                
                    ->label('URL'),
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
            SavoirfairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetences::route('/'),
            'create' => Pages\CreateCompetence::route('/create'),
            'edit' => Pages\EditCompetence::route('/{record}/edit'),
        ];
    }
}
