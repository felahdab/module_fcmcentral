<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(100)
                    ->default(''),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default(''),
                Forms\Components\DatePicker::make('date_embarq'),
                Forms\Components\DatePicker::make('date_debarq'),
                Forms\Components\TextInput::make('photo')
                    ->maxLength(256)
                    ->default(null),
                Forms\Components\TextInput::make('grade_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('specialite_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('diplome_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('secteur_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('unite_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('unite_destination_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('user_comment')
                    ->maxLength(500)
                    ->default(null),
                Forms\Components\TextInput::make('display_name')
                    ->required()
                    ->maxLength(200)
                    ->default(''),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->default(null),
                Forms\Components\Toggle::make('comete')
                    ->required(),
                Forms\Components\Toggle::make('socle')
                    ->required(),
                Forms\Components\Toggle::make('admin')
                    ->required(),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->maxLength(36)
                    ->default(null),
                Forms\Components\Textarea::make('data')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
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
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('matricule')
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
                Tables\Columns\TextColumn::make('unite_destination_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_comment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nid')
                    ->searchable(),
                Tables\Columns\IconColumn::make('comete')
                    ->boolean(),
                Tables\Columns\IconColumn::make('socle')
                    ->boolean(),
                Tables\Columns\IconColumn::make('admin')
                    ->boolean(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
