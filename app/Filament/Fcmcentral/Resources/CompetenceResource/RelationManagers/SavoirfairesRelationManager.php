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
            ->defaultSort('ordre','asc')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->words(8)
                    ->sortable(),
                Tables\Columns\TextColumn::make('competence.code')
                    ->label('Competences'),
                    Tables\Columns\TextColumn::make('competence.parcoursType.code')
                    ->label('Type de Parcours'),    
                

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
