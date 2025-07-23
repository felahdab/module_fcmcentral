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
    protected static ?string $title = 'Compétences attachées';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select ::make('recordId')
                    ->label('Fonction')
                    ->otpions(Competence::all()->pluck('libelle_court','id'))
                    ->searchable()
                    ->required()
                    ->disabled(fn ($context): bool => $context === 'edit'),
                Forms\Components\TextInput::make('same')
                    ->label('Niveau SAME')
                    ->maxLength(50),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->columns([
                Tables\Columns\TextColumn::make('libelle_court')
                    ->label('Libellé court'),
                Tables\Columns\TextColumn::make('same')
                    ->label('Niveau SAME'),
                Tables\Columns\TextColumn::make('pivot.ordre')
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
                    Forms\Components\TextInput::make('same')
                        ->label('Niveau SAME'),     
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
                        Forms\Components\TextInput::make('same')
                        ->label('Niveau SAME'),
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
