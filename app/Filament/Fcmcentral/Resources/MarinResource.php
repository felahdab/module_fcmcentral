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

    protected static ?string $navigationGroup = 'Marins';

    protected static ?string $navigationLabel = "Tous les marins";

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->unique(
                        table: 'rh_marins',
                        column: 'email',
                        ignoreRecord : true
                    )
                    ->required(),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->required()
                    ->default(''),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default(''),
                Forms\Components\DatePicker::make('date_embarq'),
                Forms\Components\DatePicker::make('date_debarq'),
                Forms\Components\Select::make('grade_id')
                    ->relationship(name: 'grade', titleAttribute: 'libelle_long'),
                Forms\Components\Select::make('specialite_id')
                    ->relationship(name: 'specialite', titleAttribute: 'libelle_court'),
                Forms\Components\Select::make('brevet_id')
                    ->relationship(name: 'brevet', titleAttribute: 'libelle_long'),
                Forms\Components\Select::make('unite_id')
                    ->relationship(name: 'unite', titleAttribute: 'libelle_long'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.cohorte.libelle_court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.mentor.nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suiviEnFcm')
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
                Tables\Columns\TextColumn::make('grade_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialite_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brevet_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unite_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_sirh_user')
                    ->searchable(),
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
            'index' => Pages\ListMarins::route('/'),
            //'create' => Pages\CreateMarin::route('/create'),
            //'edit' => Pages\EditMarin::route('/{record}/edit'),
        ];
    }
}
