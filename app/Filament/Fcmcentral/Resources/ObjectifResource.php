<?php
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource\Pages;
use Modules\FcmCentral\Models\{Objectif, Competence, Savoirfaire, CompetenceSavoirfaire};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;

class ObjectifResource extends Resource
{
    use FilamentPermissionTrait;

    protected static ?string $model = Objectif::class;

    protected static array $permissions = [
        'fcmcentral::objectif.index',
        'fcmcentral::admin',
        'fcmcentral::permission.fps',
        'fcmcentral::permission.bcf',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('libelle_court')
                ->label('Libellé court')
                ->required()
                ->maxLength(150),

            Forms\Components\TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(150),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Placeholder::make('competenceSavoirfaire_actuel')
                    ->label('Compétence / Savoir-Faire actuel')
                    ->content(fn ($record) =>
                        $record?->competenceSavoirfaire
                            ? "{$record->competenceSavoirfaire->competence->code} - {$record->competenceSavoirfaire->savoirfaire->code}"
                            : 'Non défini'
                    ),
                Forms\Components\Placeholder::make('vide')->content('')->label(''),
            ]),

            Forms\Components\Placeholder::make('info')
                ->content('Pour changer la catégorie, veuillez sélectionner la compétence puis le savoir-faire.'),

            Forms\Components\Select::make('temp_competence_id')
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

            Forms\Components\Select::make('competence_savoirfaire_id')
                ->label('Nouveau savoir-faire')
                ->options(function (callable $get, $record) {
                    $competenceId = $get('temp_competence_id');
                    
                    // Si pas de compétence sélectionnée et enregistrement existant
                    if (!$competenceId && $record?->competenceSavoirfaire) {
                        return CompetenceSavoirfaire::with(['competence', 'savoirfaire'])
                            ->where('competence_id', $record->competenceSavoirfaire->competence_id)
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

                    // Liste des savoir-faire de la compétence sélectionnée
                    return CompetenceSavoirfaire::with(['competence', 'savoirfaire'])
                        ->where('competence_id', $competenceId)
                        ->get()
                        ->mapWithKeys(fn($item) => [
                            $item->id => "{$item->competence->code} - {$item->savoirfaire->code}"
                        ])
                        ->toArray();
                })
                ->helperText('Veuillez sélectionner le savoir-faire')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->color ?? 'info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('libelle_court')
                    ->label('Libellé court')
                    ->limit(100)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('competenceSavoirfaire.competence.code')
                    ->label('Compétence')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competenceSavoirfaire?->competence?->color ?? 'info'),

                Tables\Columns\TextColumn::make('competenceSavoirfaire.savoirfaire.code')
                    ->label('Savoir Faire')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->competenceSavoirfaire?->savoirfaire?->color ?? 'info'),

                Tables\Columns\TextColumn::make('competenceSavoirfaire.savoirfaireType.code')
                    ->label('Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->modalHeading('Détail')
                    ->extraAttributes([
                        'title' => 'Consulter',
                        'class' => 'btn-consult',
                    ]),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObjectifs::route('/'),
            'create' => Pages\CreateObjectif::route('/create'),
            'edit' => Pages\EditObjectif::route('/{record}/edit'),
        ];
    }
}
