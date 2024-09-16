<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\RelationManagers;
use Modules\FcmCentral\Models\Marin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarinResource extends Resource
{
    protected static ?string $model = Marin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Marins en FCM';

    protected static ?string $navigationGroup = 'Marins';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->maxLength(255)
                    ->default(null),
                    Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(100)
                    ->default(''),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default(''),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('matricule')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_embarq')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debarq')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('photo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialite_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('diplome_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('secteur_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unite_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('livret-de-fcm')
                                    ->label("Livret de FCM")
                                    ->url(fn (Marin $record) : string => static::getUrl('livret-de-fcm', ['record' => $record]))
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
            //ParcoursAttribuesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarins::route('/'),
            'create' => Pages\CreateMarin::route('/create'),
            'view' => Pages\ViewMarin::route('/{record}'),
            'edit' => Pages\EditMarin::route('/{record}/edit'),
            'livret-de-fcm' => Pages\LivretDeFcm::route('/{record}/livret-de-fcm'),
        ];
    }
}
