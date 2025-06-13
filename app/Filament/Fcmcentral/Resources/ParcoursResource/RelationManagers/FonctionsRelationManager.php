<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\FcmCentral\Models\Fonction;


class FonctionsRelationManager extends RelationManager
{
    protected static string $relationship = 'fonctions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select ::make('recordId')
                ->label('Fonction')
                ->otpions(Fonction::all()->pluck('libelle_long','id'))
                ->searchable()
                ->required()
                ->disabled(fn ($context): bool => $context === 'edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_long')
            ->columns([
                TextColumn::make('libelle_long')
                ->label('Fonction Court'),
                TextColumn::make('libelle_long')
                ->label('Fonction'),
               

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
