<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages\CreateCohorte;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages\EditCohorte;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages\ListCohortes;
use Modules\FcmCentral\Models\Cohorte;

class CohorteResource extends Resource
{
    protected static ?string $model = Cohorte::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    // ->maxLength(255)
                    ->default(null)
                    ->label('Libellé court')
                    ->required(),
                TextInput::make('libelle_long')
                   // ->maxLength(255)
                    ->default(null)
                    ->label('Libellé long')
                    ->required(),
                DatePicker::make('date_sortie_cours')
                    ->label('Date fin'),
            ])
        ;
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
                    ->getStateUsing(function (Cohorte $record) {
                        return $record->fcmCentralMarins()->count();
                    })
                    ->sortable()
                    ->badge(),
                TextColumn::make('date_sortie_cours')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
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
            'index' => ListCohortes::route('/'),
            'create' => CreateCohorte::route('/create'),
            'edit' => EditCohorte::route('/{record}/edit'),
        ];
    }
}
