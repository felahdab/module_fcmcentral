<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\FcmCentral\Models\Competence;
use Filament\Forms\Components\Select;

class CompetencesRelationManager extends RelationManager
{
    protected static string $relationship = 'competences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select ::make('recordId')
                ->label('Fonction')
                ->otpions(Competence::all()->pluck('libelle_long','id'))
                ->searchable()
                ->required()
                ->disabled(fn ($context): bool => $context === 'edit'),
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
