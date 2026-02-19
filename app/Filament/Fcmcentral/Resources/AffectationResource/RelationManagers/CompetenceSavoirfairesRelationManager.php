<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Illuminate\Support\Str;

class CompetenceSavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'competenceSavoirfaires';
    protected static ?string $title = 'Savoirs Faire (via compétences)';
    protected static ?string $recordTitleAttribute = 'libelle_court';

    public function form(Schema $schema): Schema
    {
        return $schema
        ->components([
            Select::make('competence_id')
                ->label('Compétence')
                ->options(fn() => $this->ownerRecord->competences->pluck('libelle_court', 'id')->toArray())
                ->reactive()
                ->required()
                ->afterStateUpdated(fn ($state, callable $set) => $set('savoirfaire_id', null)),

            Select::make('savoirfaire_id')
                ->label('Savoir faire')
                ->options(fn (callable $get) => (function () use ($get) {
                    $compId = $get('competence_id');
                    if (! $compId) {
                        return []; // aucune option tant que compétence non choisie
                    }
                    return Savoirfaire::where('competence_id', $compId)
                        ->orderBy('ordre')
                        ->pluck('libelle_court', 'id')
                        ->toArray();
                })())
                ->searchable()
                ->required()
                ->disabled(fn (callable $get) => ! (bool) $get('competence_id')),

            Select::make('savoirfaire_type_id')
                ->label('Type de Savoir-faire')
                ->options(fn() => SavoirfaireType::pluck('libelle_court', 'id')->toArray())
                ->searchable(),

            TextInput::make('libelle_court')->label('Intitulé')->maxLength(255),
            TextInput::make('ordre')->label('Ordre')->numeric()->default(1),
            TextInput::make('heures')->label('Heures')->numeric(),
            TextInput::make('jours')->label('Jours')->numeric(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('competence.code')->label('Compétence')->badge(),
                TextColumn::make('savoirfaire.code')->label('SF')->badge(),
                TextColumn::make('libelle_court')->label('Intitulé')->limit(60),
                TextColumn::make('ordre')->label('Ordre')->numeric()->sortable(),
                TextColumn::make('heures')->label('Heures')->numeric(),
                TextColumn::make('jours')->label('Jours')->numeric(),
                TextColumn::make('savoirfaireType.libelle_court')->label('Type'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter Savoir faire')
                    ->mutateDataUsing(fn(array $data) => $data + ['uuid' => Str::uuid()->toString()])
                    ->after(function (CompetenceSavoirfaire $record) {
                        // hook si nécessaire
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}



