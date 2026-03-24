<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages\ListObjectifs;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages\CreateObjectif;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages\EditObjectif;
use Modules\FcmCentral\Models\{Objectif, Competence, Savoirfaire, CompetenceSavoirfaire};
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class ObjectifResource extends Resource
{
    protected static ?string $model = Objectif::class;
    protected static string | \UnitEnum | null $navigationGroup = "Catalogue";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->columns(1)
        ->components([

            // ─── Section 1 : Identification ───────────────────────────
            Section::make('Identification')
                ->description('Informations principales de l\'objectif')
                ->schema([
                    TextInput::make('libelle_court')
                        ->label('Libellé')
                        ->required()
                        //->maxLength(150)
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            //->maxLength(150)
                            ,

                        TextInput::make('heures')
                            ->label('Heures')
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0)
                            ->suffix('h')
                            ->placeholder('ex : 7.5'),

                        TextInput::make('ordre')
                            ->label('Ordre')
                            ->numeric()
                            ->minValue(0),
                    ]),
                ]),

            // ─── Section 2 : Rattachement actuel ──────────────────────
            Section::make('Rattachement actuel')
                ->description('Compétence et savoir-faire auxquels cet objectif est rattaché')
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('competence_actuelle')
                            ->label('Compétence')
                            ->content(fn ($record) =>
                                $record?->competenceSavoirfaire?->competence
                                    ? "{$record->competenceSavoirfaire->competence->code}"
                                    : '—'
                            ),

                        Placeholder::make('savoirfaire_actuel')
                            ->label('Savoir-faire')
                            ->content(fn ($record) =>
                                $record?->competenceSavoirfaire?->savoirfaire
                                    ? "{$record->competenceSavoirfaire->savoirfaire->code}"
                                    : '—'
                            ),

                        Placeholder::make('type_actuel')
                            ->label('Type')
                            ->content(fn ($record) =>
                                $record?->competenceSavoirfaire?->savoirfaireType?->libelle_court
                                    ?? '—'
                            ),
                    ]),
                ])
                ->collapsed(false),

            // ─── Section 3 : Changer le rattachement ──────────────────
            Section::make('Changer le rattachement')
                ->description('Sélectionnez une compétence puis le savoir-faire cible. Laissez vide pour ne pas modifier.')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('temp_competence_id')
                            ->label('Nouvelle compétence')
                            ->placeholder('— Sélectionner —')
                            ->options(fn () =>
                                Competence::orderBy('code')
                                    ->pluck('code', 'id')
                                    ->toArray()
                            )
                            ->helperText('Filtre les savoir-faire disponibles ci-dessous')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('competence_savoirfaire_id', null);

                                if ($state) {
                                    $firstSf = CompetenceSavoirfaire::where('competence_id', $state)
                                        ->whereHas('savoirfaireType', fn($q) => $q->where('code', 'C'))
                                        ->first();
                                    $set('competence_savoirfaire_id', $firstSf?->id);
                                }
                            }),

                        Select::make('competence_savoirfaire_id')
                            ->label('Savoir-faire (type Compagnonnage uniquement)')
                            ->placeholder('— Sélectionner d\'abord une compétence —')
                            ->options(function (callable $get, $record) {
                                $competenceId = $get('temp_competence_id');

                                // Pas de compétence choisie → afficher ceux du rattachement actuel
                                if (!$competenceId && $record?->competenceSavoirfaire) {
                                    return CompetenceSavoirfaire::with(['competence', 'savoirfaire', 'savoirfaireType'])
                                        ->where('competence_id', $record->competenceSavoirfaire->competence_id)
                                        ->whereHas('savoirfaireType', fn($q) => $q->where('code', 'C'))
                                        ->get()
                                        ->mapWithKeys(fn($item) => [
                                            $item->id => "{$item->competence->code} — {$item->savoirfaire->code}"
                                        ])
                                        ->toArray();
                                }

                                if (!$competenceId) return [];

                                return CompetenceSavoirfaire::with(['competence', 'savoirfaire', 'savoirfaireType'])
                                    ->where('competence_id', $competenceId)
                                    ->whereHas('savoirfaireType', fn($q) => $q->where('code', 'C'))
                                    ->get()
                                    ->mapWithKeys(fn($item) => [
                                        $item->id => "{$item->competence->code} — {$item->savoirfaire->code}"
                                    ])
                                    ->toArray();
                            })
                            ->helperText('Seuls les savoir-faire de type Compagnonnage (C) sont listés')
                            ->live()
                            ->searchable()
                            ->required(),
                    ]),
                ])
                ->collapsible()
                ->collapsed(fn ($record) => $record !== null), // fermé par défaut en édition
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre', 'asc')
            ->reorderable('ordre')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->color ?? 'info')
                    ->searchable(),

                TextColumn::make('libelle_court')
                    ->label('Libellé')
                    //->limit(80)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('heures')
                    ->label('Heures')
                    ->suffix('h')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('competenceSavoirfaire.competence.code')
                    ->label('Compétence')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competenceSavoirfaire?->competence?->color ?? 'info'),

                TextColumn::make('competenceSavoirfaire.savoirfaire.code')
                    ->label('Savoir-faire')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competenceSavoirfaire?->savoirfaire?->color ?? 'info'),

                TextColumn::make('competenceSavoirfaire.savoirfaireType.code')
                    ->label('Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('competence')
                    ->label('Compétence')
                    ->options(fn () => Competence::orderBy('code')->pluck('code', 'id')->toArray())
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $value) => $q->whereHas(
                                'competenceSavoirfaire',
                                fn($q) => $q->where('competence_id', $value)
                            )
                        )
                    ),

                SelectFilter::make('savoirfaire')
                    ->label('Savoir-faire')
                    ->options(fn () => Savoirfaire::orderBy('code')->pluck('code', 'id')->toArray())
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $value) => $q->whereHas(
                                'competenceSavoirfaire',
                                fn($q) => $q->where('savoirfaire_id', $value)
                            )
                        )
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détail')
                    ->extraAttributes(['title' => 'Consulter']),

                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes(['title' => 'Modifier']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObjectifs::route('/'),
            'create' => CreateObjectif::route('/create'),
            'edit' => EditObjectif::route('/{record}/edit'),
        ];
    }
}
