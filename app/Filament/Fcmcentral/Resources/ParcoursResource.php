<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;
use Modules\FcmCentral\Models\Parcours;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParcoursResource extends Resource
{
    protected static ?string $model = Parcours::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Parcours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('libelle_long')
                    ->searchable(),
                Tables\Columns\TextColumn::make('libelle_court')
                    ->searchable(),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListParcours::route('/'),
            'create' => Pages\CreateParcours::route('/create'),
            'edit' => Pages\EditParcours::route('/{record}/edit'),
        ];
    }
}
