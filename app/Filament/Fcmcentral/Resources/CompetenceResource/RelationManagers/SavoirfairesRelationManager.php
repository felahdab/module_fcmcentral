<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'savoirfaires';
    protected static ?string $title = 'Savoir-faire attachés';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->required()
                    ->maxLength(150),
                TextInput::make('libelle_long')
                    ->required()
                    ->maxLength(1500),
            ])
        ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle_court')
            ->defaultSort('ordre', 'asc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->words(8)
                    ->sortable(),
                TextColumn::make('competence.code')
                    ->label('Competences'),
                TextColumn::make('competence.parcoursType.code')
                    ->label('Type de Parcours'),
            ])
            ->filters([
            ])
            ->headerActions([
                AttachAction::make()
                    ->modalHeading('Attacher savoir-faire')
                    ->color('primary')
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('mod_acquis')
                            ->label('Mode acquisition')
                            ->required()
                            ->options([
                                'COMPAGNONNAGE' => 'COMPAGNONNAGE',
                                'ECOLE' => 'ECOLE',
                                'EAD' => 'EAD',
                                'MIXTE' => 'MIXTE',
                            ]),
                        TextInput::make('an_acquis')
                            ->label('Année acquisition')
                            ->numeric()
                            ->required(),
                        TextInput::make('coeff')
                            ->label('Coefficient')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('duree')
                            ->label('Durée(h)')
                            ->numeric(),
                        TextInput::make('ordre')
                            ->label('Ordre')
                            ->numeric(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ])
                    ->schema([
                        Select::make('mod_acquis')
                            ->label('Mode acquisition')
                            ->required()
                            ->options([
                                'COMPAGNONNAGE' => 'COMPAGNONNAGE',
                                'ECOLE' => 'ECOLE',
                                'EAD' => 'EAD',
                            ]),
                        TextInput::make('an_acquis')
                            ->label('Année acquisition')
                            ->numeric()
                            ->required(),
                        TextInput::make('coeff')
                            ->label('Coefficient')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('duree')
                            ->label('Durée(h)')
                            ->numeric(),
                        TextInput::make('ordre')
                            ->label('Ordre')
                            ->numeric(),
                    ]),
                DetachAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->extraAttributes([
                        'title' => 'Détacher',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
        ;
    }
}
