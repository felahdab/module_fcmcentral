<?php
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
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
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;
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
        return $schema->components([
            TextInput::make('libelle_court')
                ->label('Libellé court')
                ->required()
                ->maxLength(150),

            TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(150),

            Grid::make(2)->schema([
                Placeholder::make('competenceSavoirfaire_actuel')
                    ->label('Compétence / Savoir-Faire actuel')
                    ->content(fn ($record) =>
                        $record?->competenceSavoirfaire
                            ? "{$record->competenceSavoirfaire->competence->code} - {$record->competenceSavoirfaire->savoirfaire->code}"
                            : 'Non défini'
                    ),
                Placeholder::make('vide')->content('')->label(''),
            ]),

            Placeholder::make('info')
                ->content('Pour changer la catégorie, veuillez sélectionner la compétence puis le savoir-faire.'),

            Select::make('temp_competence_id')
                ->label('Nouvelle compétence')
                ->options(fn () => Competence::orderBy('code')->pluck('code', 'id')->toArray())
                ->helperText('Veuillez sélectionner la compétence')
                ->reactive()
                ->dehydrated(false)
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    // Réinitialise le savoir-faire
                    $set('competence_savoirfaire_id', null);
                    
                    // Sélectionne le premier savoir-faire de la nouvelle compétence
                    if ($state) {
                        $firstSf = CompetenceSavoirfaire::where('competence_id', $state)->first();
                        $set('competence_savoirfaire_id', $firstSf?->id);
                    }
                }),

                Select::make('competence_savoirfaire_id')
                ->label('Nouveau savoir-faire')
                ->options(function (callable $get, $record) {
                    $competenceId = $get('temp_competence_id');
                   
                    // Si pas de compétence sélectionnée et enregistrement existant
                    if (!$competenceId && $record?->competenceSavoirfaire) {
                        return CompetenceSavoirfaire::with(['competence', 'savoirfaire', 'savoirfaireType'])
                            ->where('competence_id', $record->competenceSavoirfaire->competence_id)
                            ->whereHas('savoirfaireType', fn($q) => $q->where('code', 'C'))
                            ->get()
                            ->mapWithKeys(fn($item) => [
                                $item->id => "{$item->competence->code} - {$item->savoirfaire->code}"
                            ])
                            ->toArray();
                    }
            
                    // Si pas de compétence sélectionnée
                    if (!$competenceId) {
                        return [];
                    }
            
                    // Liste des savoir-faire de type C de la compétence sélectionnée
                    return CompetenceSavoirfaire::with(['competence', 'savoirfaire', 'savoirfaireType'])
                        ->where('competence_id', $competenceId)
                        ->whereHas('savoirfaireType', fn($q) => $q->where('code', 'C'))
                        ->get()
                        ->mapWithKeys(fn($item) => [
                            $item->id => "{$item->competence->code} - {$item->savoirfaire->code}"
                        ])
                        ->toArray();
                })
                ->helperText('Seuls les savoir-faire de type Compagnonnage (C) sont disponibles')
                ->reactive()
                ->searchable()
                ->required(),
            
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
                    ->label('Libellé court')
                    ->limit(100)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('competenceSavoirfaire.competence.code')
                    ->label('Compétence')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competenceSavoirfaire?->competence?->color ?? 'info'),

                TextColumn::make('competenceSavoirfaire.savoirfaire.code')
                    ->label('Savoir Faire')
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
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'competenceSavoirfaire',
                                fn($q) => $q->where('competence_id', $value)
                            )
                        );
                    }),

                SelectFilter::make('savoirfaire')
                    ->label('Savoir-Faire')
                    ->options(fn () => Savoirfaire::orderBy('code')->pluck('code', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'competenceSavoirfaire',
                                fn($q) => $q->where('savoirfaire_id', $value)
                            )
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détail')
                    ->extraAttributes([
                        'title' => 'Consulter',
                        'class' => 'btn-consult',
                    ]),

                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
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
