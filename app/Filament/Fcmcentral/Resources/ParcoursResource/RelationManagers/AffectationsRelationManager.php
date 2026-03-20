<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AffectationsRelationManager extends RelationManager
{
    protected static string $relationship = 'affectations';
    protected static ?string $title = 'Affectations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(150),
            TextInput::make('libelle_court')->required()->maxLength(150)->label('Libellé court'),
            TextInput::make('libelle_long')->maxLength(1500)->label('Libellé long'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->reorderable('fcmcentral_affectation_parcours.ordre') // ✅ table qualifiée
        ->defaultSort('fcmcentral_affectation_parcours.ordre', 'asc') // ✅
        ->columns([
                TextColumn::make('ordre') // ✅ affiche l'ordre du pivot
                ->label('Ordre')
                ->sortable(),
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('libelle_court')->searchable()->label('Libellé'),
                TextColumn::make('competences_count')
                    ->label('Compétences')
                    ->badge()
                    ->color('info')
                    ->state(function ($record) {
                        // ✅ filtre par parcours_type_id
                        return $record->competences()
                            ->where('parcours_type_id', $this->ownerRecord->parcours_type_id)
                            ->count();
    }),

            ])
            ->headerActions([
                AttachAction::make()
                ->preloadRecordSelect()
                ->recordSelectOptionsQuery(fn ($query) => $query->orderBy('libelle_court'))
                ->recordSelectSearchColumns(['libelle_court', 'code']) // ✅ colonnes de recherche
                ->recordTitleAttribute('libelle_court'), // ✅ affiche libelle_court dans le select

                CreateAction::make()
                    ->mutateFormDataUsing(fn ($data) => array_merge($data, ['uuid' => Str::uuid()])),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}
