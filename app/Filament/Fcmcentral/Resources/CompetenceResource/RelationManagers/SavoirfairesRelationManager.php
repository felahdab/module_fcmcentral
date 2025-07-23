<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'savoirfaires';
    protected static ?string $title = 'Savoir-faire attachés';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(1500),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->defaultSort('libelle_court','asc')
            ->columns([
                Tables\Columns\TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.mod_acquis')
                    ->label('Mode acq.'),
                Tables\Columns\TextColumn::make('pivot.an_acquis')
                    ->label('Année acq.'),
                Tables\Columns\TextColumn::make('pivot.ordre')
                    ->label('Ordre'),
                Tables\Columns\TextColumn::make('pivot.coeff')
                    ->label('Coefficient'),
                Tables\Columns\TextColumn::make('pivot.duree')
                    ->label('Durée(h)'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->modalHeading('Attacher savoir-faire')
                    ->color('primary')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('mod_acquis')
                            ->label('Mode acquisition')
                            ->required()
                            ->options([
                                'COMPAGNONNAGE' => 'COMPAGNONNAGE',
                                'ECOLE' => 'ECOLE',
                                'EAD' => 'EAD',
                                'MIXTE' => 'MIXTE',
                                ]),                         
                        Forms\Components\TextInput::make('an_acquis')
                            ->label('Année acquisition')
                            ->numeric()
                            ->required(),  
                        Forms\Components\TextInput::make('coeff')
                            ->label('Coefficient')
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('duree')
                            ->label('Durée(h)')
                            ->numeric(),     
                        Forms\Components\TextInput::make('ordre')
                            ->label('Ordre')
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
                    Forms\Components\Select::make('mod_acquis')
                        ->label('Mode acquisition')
                        ->required()
                        ->options([
                            'COMPAGNONNAGE' => 'COMPAGNONNAGE',
                            'ECOLE' => 'ECOLE',
                            'EAD' => 'EAD',
                            ]),                         
                    Forms\Components\TextInput::make('an_acquis')
                        ->label('Année acquisition')
                        ->numeric()
                        ->required(),  
                    Forms\Components\TextInput::make('coeff')
                        ->label('Coefficient')
                        ->numeric()
                        ->step(0.01),
                    Forms\Components\TextInput::make('duree')
                        ->label('Durée(h)')
                        ->numeric(),     
                    Forms\Components\TextInput::make('ordre')
                        ->label('Ordre')
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
