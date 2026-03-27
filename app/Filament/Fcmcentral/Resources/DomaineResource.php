<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages\CreateDomaine;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages\EditDomaine;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\Pages\ListDomaines;
use Modules\FcmCentral\Helpers\OutilsHelper;
use Modules\FcmCentral\Models\Domaine;

class DomaineResource extends Resource
{
    protected static ?string $model = Domaine::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->required()
                   // ->maxLength(150)
                    ->label('Libellé court'),
                TextInput::make('libelle_long')
                    ->required()
                   // ->maxLength(1500)
                    ->label('Libellé long'),
                TextInput::make('code')

                    ->label('Code')
                // ->maxLength(150)
                ,
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
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
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
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détail')
                    ->extraAttributes([
                        'title' => 'Consulter',
                        'class' => 'btn-consult',
                    ]),
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
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
            'index' => ListDomaines::route('/'),
            'create' => CreateDomaine::route('/create'),
            'edit' => EditDomaine::route('/{record}/edit'),
        ];
    }
}
