<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\FcmCentral\Helpers\OutilsHelper;

class AffectationsRelationManager extends RelationManager
{
    protected static string $relationship = 'affectations';
    protected static ?string $title = 'Affectations (étape 1/3)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->maxLength(150),
            TextInput::make('libelle_court')
                ->required()
                ->maxLength(150)
                ->label('Libellé court'),
            TextInput::make('libelle_long')
                ->maxLength(1500)
                ->label('Libellé long'),
            Select::make('color')
                ->label('Couleur du badge')
                ->options(OutilsHelper::selectColors()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Les affectations constituent la structure du parcours. Chaque affectation regroupe des compétences.')
            ->reorderable('fcmcentral_affectation_parcours.ordre')
            ->defaultSort('fcmcentral_affectation_parcours.ordre', 'asc')
            ->columns([
                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->color ?? 'info'),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libellé'),
                TextColumn::make('competences_count')
                    ->label('Compétences')
                    ->badge()
                    ->color('info')
                    ->state(function ($record) {
                        return $record->competences()
                            ->where('parcours_type_id', $this->ownerRecord->parcours_type_id)
                            ->count();
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->orderBy('libelle_court'))
                    ->recordSelectSearchColumns(['libelle_court', 'code'])
                    ->recordTitleAttribute('libelle_court'),

                CreateAction::make()
                    ->mutateFormDataUsing(fn ($data) => array_merge($data, ['uuid' => Str::uuid()])),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}


