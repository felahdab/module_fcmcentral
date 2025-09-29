<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class SavoirfaireRelationManager extends RelationManager
{
    protected static string $relationship = 'savoirfaires';

    protected static ?string $title = 'Savoir-faire';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle_court')
                    ->required()
                    ->label('Libellé court')
                    ->maxLength(150),
                TextInput::make('libelle_long')
                    ->required()
                    ->label('Libellé long')
                    ->maxLength(1500),
                TextInput::make('url')
                    ->label('URL')
                    ->maxLength(1000),
                TextInput::make('code_sicomp')
                    ->label('Code OCTAVE')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->columns([
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->sortable()
                    ->label('Libellé court'),
                TextColumn::make('libelle_long')
                    ->searchable()
                    ->label('Libellé long')
                    ->limit(50),
                TextColumn::make('code_sicomp')
                    ->label('Code OCTAVE'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
