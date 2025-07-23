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
                ->otpions(Fonction::all()->pluck('libelle_court','id'))
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
                TextColumn::make('libelle_court')
                ->label('Libellé court'),
                TextColumn::make('libelle_long')
                ->label('Libellé long'),
                TextColumn::make('pivot.ordre')
                ->label('Ordre')
                ->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->color('primary')
                ->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    Forms\Components\TextInput::make('ordre')
                        ->label('Ordre')
                        ->required()
                        ->numeric(),     
                ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                        ])
                    ->form([
                        Forms\Components\TextInput::make('ordre')
                        ->label('Ordre')
                        ->required()
                        ->numeric(),
                    ]),
                Tables\Actions\DetachAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->extraAttributes([
                        'title' => 'Détacher',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
