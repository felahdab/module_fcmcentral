<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages\CreateParcoursType;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages\EditParcoursType;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource\Pages\ListParcoursTypes;
use Modules\FcmCentral\Helpers\OutilsHelper;
use Modules\FcmCentral\Models\ParcoursType;

class ParcoursTypeResource extends Resource
{
    protected static ?string $model = ParcoursType::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 10;

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
                // ->maxLength(150)
                    ->label('code'),
                TextInput::make('libelle_court')
                    ->required()
                // ->maxLength(150)
                    ->label('Libellé court'),
                TextInput::make('libelle_long')
                    ->required()
                    // ->maxLength(1500)
                    ->label('Libellé long'),
                Select::make('color')
                    ->options(OutilsHelper::selectColors())
                    ->label('Couleur du Badge'),
            ])
        ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre', 'asc')
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
                    ->color(fn ($record) => $record->color ?? $record->competence->color ?? 'info'),

                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    // ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => substr($record->libelle_long, 0, 40).'...'),
                TextColumn::make('ordre'),
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

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParcoursTypes::route('/'),
            'create' => CreateParcoursType::route('/create'),
            'edit' => EditParcoursType::route('/{record}/edit'),
        ];
    }
}
