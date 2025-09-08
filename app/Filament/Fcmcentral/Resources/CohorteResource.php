<?php

namespace Modules\Fcmcentral\Filament\Fcmcentral\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Traits\FcmAccessControl;

class CohorteResource extends Resource
{
    
    use FcmAccessControl;
    
    protected static ?string $model = Cohorte::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Gestion Cohortes';


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
    
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    } 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_court')
                    ->maxLength(255)
                    ->default(null)
                    ->label('Libellé court')
                    ->required(),
                TextInput::make('libelle_long')
                    ->maxLength(255)
                    ->default(null)
                    ->label('Libellé long')
                    ->required(),
                DatePicker::make('date_sortie_cours')
                    ->label('Date fin')
                    ->required(),    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cohorte_count')
                    ->label('Nb Marins')
                    ->getStateUsing(function (Cohorte $record){
                        return $record->fcmCentralMarins()->count();
                    })
                    ->sortable()
                    ->badge(),
                TextColumn::make('date_sortie_cours')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListCohortes::route('/'),
            'create' => Pages\CreateCohorte::route('/create'),
            'edit' => Pages\EditCohorte::route('/{record}/edit'),
        ];
    }
}
