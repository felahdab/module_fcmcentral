<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages\CreateTypeGel;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages\EditTypeGel;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource\Pages\ListTypeGels;
use Modules\FcmCentral\Models\TypeEtat;

class TypeGelResource extends Resource
{
    protected static ?string $model = TypeEtat::class;
    protected static ?string $navigationLabel = 'Type de  Gels';
    protected static ?string $label = 'Type de Gel';
    protected static ?string $plurialLabel = 'Type de Gels';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    // ✅ Vérifier si l'utilisateur est admin
    public static function canAccess(): bool
    {
        return auth()->user()?->admin ?? false;
    }

    // ✅ Optionnel : Masquer aussi dans la navigation si pas admin
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->label('Code')
                // ->maxLength(150)
                ,

                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                // ->maxLength(150)
                ,
            ])
        ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('libelle_court', 'asc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé'),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypeGels::route('/'),
            'create' => CreateTypeGel::route('/create'),
            'edit' => EditTypeGel::route('/{record}/edit'),
        ];
    }
}
