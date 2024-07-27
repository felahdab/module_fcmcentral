<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Actions\AttachAction;

class ParcoursAttribuesRelationManager extends RelationManager
{
    protected static string $relationship = 'parcours_attribues';

    protected static ?string $title = 'Parcours attribués à cet utilisateur';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->columns([
                Tables\Columns\TextColumn::make('libelle_court'),
                Tables\Columns\TextColumn::make('libelle_long'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
                AttachAction::make('attribuer')
                    ->label('Attribuer un parcours'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //Tables\Actions\BulkActionGroup::make([
                //    Tables\Actions\DeleteBulkAction::make(),
                //]),
            ]);
    }
}
