<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;

class SavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'competenceSavoirfaires';
    protected static ?string $title = 'Savoir-faires & Objectifs (étape 3/3)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $parcours = $this->ownerRecord;

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id')->toArray())
            ->pluck('id')
            ->toArray();

        return $table
            ->description('Les savoir-faires sont rattachés aux compétences du parcours. Chaque savoir-faire peut avoir des objectifs (si type C).')
            ->query(
                CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)
                    ->with(['competence.affectation', 'savoirfaire', 'savoirfaireType', 'objectifs'])
            )
            ->reorderable('ordre')
            ->defaultSort('ordre', 'asc')
            ->columns([
                TextColumn::make('ordre')
                    ->label('#')
                    ->sortable()
                    ->width('40px'),
                TextColumn::make('competence.affectation.libelle_court')
                    ->label('Affectation')
                    ->badge()
                    ->color(fn ($record) => $record->competence?->affectation?->color ?? 'info'),
                TextColumn::make('competence.libelle_court')
                    ->label('Compétence')
                    ->badge()
                    ->color(fn ($record) => $record->competence?->color ?? 'info'),
                TextColumn::make('savoirfaire.code')
                    ->label('Code SF')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('libelle_court')
                    ->label('Libellé contexte')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('savoirfaireType.libelle_court')
                    ->label('Type')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('heures')
                    ->label('H')
                    ->placeholder('—'),
                TextColumn::make('jours')
                    ->label('J')
                    ->placeholder('—'),
                TextColumn::make('objectifs_count')
                    ->counts('objectifs')
                    ->label('Obj.')
                    ->badge()
                    ->color('success'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Ajouter un savoir-faire')
                    ->modalHeading('Ajouter un savoir-faire')
                    ->modalWidth('2xl')
                    ->schema(fn (Schema $schema) => $schema->components([

                        Section::make('1 — Rattachement')
                            ->description('À quelle compétence appartient ce savoir-faire ?')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Select::make('competence_id')
                                    ->label('Compétence')
                                    ->options(
                                        Competence::whereIn('id', $competenceIds)
                                            ->with('affectation')
                                            ->get()
                                            ->mapWithKeys(fn ($c) => [
                                                $c->id => ($c->affectation?->libelle_court ?? '?').' › '.$c->libelle_court,
                                            ])
                                    )
                                    ->required()
                                    ->searchable()
                                    ->live(),
                            ]),

                        Section::make('2 — Savoir-faire')
                            ->description('Sélectionnez un savoir-faire existant ou créez-en un nouveau.')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                Select::make('savoirfaire_id')
                                    ->label('Savoir-faire existant')
                                    ->options(
                                        fn ($get) => $get('competence_id')
                                            ? Savoirfaire::where('competence_id', $get('competence_id'))
                                                ->orderBy('libelle_court')
                                                ->pluck('libelle_court', 'id')
                                                ->toArray()
                                            : []
                                    )
                                    ->searchable()
                                    ->live()
                                    ->placeholder('— Laisser vide pour créer —'),

                                TextInput::make('nouvelle_code')
                                    ->label('Code (nouveau)')
                                    ->visible(fn ($get) => null === $get('savoirfaire_id')),

                                TextInput::make('nouvelle_libelle_court')
                                    ->label('Libellé court (nouveau)')
                                    ->visible(fn ($get) => null === $get('savoirfaire_id')),

                                TextInput::make('nouvelle_libelle_long')
                                    ->label('Libellé long (nouveau)')
                                    ->visible(fn ($get) => null === $get('savoirfaire_id')),
                            ]),

                        Section::make('3 — Contexte dans ce parcours')
                            ->description('Ces informations sont spécifiques à ce parcours et ne modifient pas le savoir-faire global.')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('savoirfaire_type_id')
                                    ->label('Type')
                                    ->options(SavoirfaireType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->live(),

                                TextInput::make('libelle_court')
                                    ->label('Libellé contexte')
                                    ->helperText('Intitulé spécifique à ce parcours. Laissez vide pour utiliser le libellé du savoir-faire.'),

                                TextInput::make('heures')
                                    ->label('Heures')
                                    ->numeric()
                                    ->suffix('h'),

                                TextInput::make('jours')
                                    ->label('Jours')
                                    ->numeric()
                                    ->suffix('j'),
                            ])
                            ->columns(2),

                        Section::make('4 — Objectifs')
                            ->description('Uniquement pour les savoir-faires de type C.')
                            ->icon('heroicon-o-check-circle')
                            ->visible(function ($get) {
                                $typeId = $get('savoirfaire_type_id');
                                if (!$typeId) {
                                    return false;
                                }
                                $type = SavoirfaireType::find($typeId);

                                return 'C' === strtoupper($type?->code ?? '');
                            })
                            ->schema([
                                Repeater::make('objectifs')
                                    ->label('')
                                    ->addActionLabel('+ Ajouter un objectif')
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('code')->label('Code')->required()->columnSpan(1),
                                        TextInput::make('libelle_court')->label('Libellé court')->required()->columnSpan(2),
                                        TextInput::make('heures')->label('Heures')->numeric()->suffix('h')->columnSpan(1),
                                    ])
                                    ->columns(4),
                            ]),

                    ]))
                    ->action(function (array $data): void {
                        if (!empty($data['savoirfaire_id'])) {
                            $savoirfaire = Savoirfaire::find($data['savoirfaire_id']);
                        } else {
                            $savoirfaire = Savoirfaire::create([
                                'uuid' => Str::uuid(),
                                'code' => $data['nouvelle_code'] ?? '',
                                'libelle_court' => $data['nouvelle_libelle_court'] ?? '',
                                'libelle_long' => $data['nouvelle_libelle_long'] ?? '',
                                'competence_id' => $data['competence_id'],
                            ]);
                        }

                        if (!$savoirfaire) {
                            return;
                        }

                        $csf = CompetenceSavoirfaire::create([
                            'uuid' => Str::uuid(),
                            'competence_id' => $data['competence_id'],
                            'savoirfaire_id' => $savoirfaire->id,
                            'savoirfaire_type_id' => $data['savoirfaire_type_id'] ?? null,
                            'libelle_court' => $data['libelle_court'] ?? null,
                            'heures' => $data['heures'] ?? null,
                            'jours' => $data['jours'] ?? null,
                        ]);

                        foreach ($data['objectifs'] ?? [] as $obj) {
                            if (empty($obj['code'])) {
                                continue;
                            }
                            $csf->objectifs()->create([
                                'uuid' => Str::uuid(),
                                'code' => $obj['code'],
                                'libelle_court' => $obj['libelle_court'] ?? '',
                                'heures' => $obj['heures'] ?? null,
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->modalHeading(fn (Model $record) => 'Modifier — '.$record->savoirfaire?->code)
                    ->modalWidth('2xl')
                    ->fillForm(fn (Model $record) => [
                        'competence_id' => $record->competence_id,
                        'savoirfaire_id' => $record->savoirfaire_id,
                        'sf_code' => $record->savoirfaire?->code,
                        'sf_libelle_court' => $record->savoirfaire?->libelle_court,
                        'sf_libelle_long' => $record->savoirfaire?->libelle_long,
                        'savoirfaire_type_id' => $record->savoirfaire_type_id,
                        'libelle_court' => $record->libelle_court,
                        'heures' => $record->heures,
                        'jours' => $record->jours,
                        'objectifs' => $record->objectifs->map(fn ($obj) => [
                            'code' => $obj->code,
                            'libelle_court' => $obj->libelle_court,
                            'heures' => $obj->heures,
                        ])->toArray(),
                    ])
                    ->schema([

                        Section::make('Rattachement')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Select::make('competence_id')
                                    ->label('Compétence')
                                    ->options(
                                        Competence::whereIn('id', $competenceIds)
                                            ->with('affectation')
                                            ->get()
                                            ->mapWithKeys(fn ($c) => [
                                                $c->id => ($c->affectation?->libelle_court ?? '?').' › '.$c->libelle_court,
                                            ])
                                    )
                                    ->required()
                                    ->searchable(),
                            ]),

                        Section::make('Savoir-faire global')
                            ->description('⚠️ Ces modifications s\'appliquent à tous les parcours utilisant ce savoir-faire.')
                            ->icon('heroicon-o-book-open')
                            ->collapsible()
                            ->schema([
                                Select::make('savoirfaire_id')
                                    ->label('Savoir-faire')
                                    ->options(
                                        fn ($get) => $get('competence_id')
                                            ? Savoirfaire::where('competence_id', $get('competence_id'))
                                                ->orderBy('libelle_court')
                                                ->pluck('libelle_court', 'id')
                                                ->toArray()
                                            : []
                                    )
                                    ->searchable()
                                    ->live(),

                                TextInput::make('sf_code')
                                    ->label('Code')
                                    ->required(),

                                TextInput::make('sf_libelle_court')
                                    ->label('Libellé court')
                                    ->required(),

                                TextInput::make('sf_libelle_long')
                                    ->label('Libellé long'),
                            ]),

                        Section::make('Contexte dans ce parcours')
                            ->description('Ces informations sont spécifiques à ce parcours.')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('savoirfaire_type_id')
                                    ->label('Type')
                                    ->options(SavoirfaireType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->live(),

                                TextInput::make('libelle_court')
                                    ->label('Libellé contexte')
                                    ->helperText('Laissez vide pour utiliser le libellé du savoir-faire.'),

                                TextInput::make('heures')->label('Heures')->numeric()->suffix('h'),
                                TextInput::make('jours')->label('Jours')->numeric()->suffix('j'),
                            ])
                            ->columns(2),

                        Section::make('Objectifs')
                            ->icon('heroicon-o-check-circle')
                            ->visible(function ($get) {
                                $typeId = $get('savoirfaire_type_id');
                                if (!$typeId) {
                                    return false;
                                }

                                return 'C' === strtoupper(SavoirfaireType::find($typeId)?->code ?? '');
                            })
                            ->schema([
                                Repeater::make('objectifs')
                                    ->label('')
                                    ->addActionLabel('+ Ajouter un objectif')
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('code')->label('Code')->required()->columnSpan(1),
                                        TextInput::make('libelle_court')->label('Libellé court')->required()->columnSpan(2),
                                        TextInput::make('heures')->label('Heures')->numeric()->suffix('h')->columnSpan(1),
                                    ])
                                    ->columns(4),
                            ]),
                    ])
                    ->action(function (Model $record, array $data): void {
                        if ($data['savoirfaire_id']) {
                            Savoirfaire::where('id', $data['savoirfaire_id'])->update([
                                'code' => $data['sf_code'],
                                'libelle_court' => $data['sf_libelle_court'],
                                'libelle_long' => $data['sf_libelle_long'] ?? null,
                            ]);
                        }

                        $record->update([
                            'competence_id' => $data['competence_id'],
                            'savoirfaire_id' => $data['savoirfaire_id'],
                            'savoirfaire_type_id' => $data['savoirfaire_type_id'] ?? null,
                            'libelle_court' => $data['libelle_court'] ?? null,
                            'heures' => $data['heures'] ?? null,
                            'jours' => $data['jours'] ?? null,
                        ]);

                        $record->objectifs()->delete();
                        foreach ($data['objectifs'] ?? [] as $obj) {
                            if (empty($obj['code'])) {
                                continue;
                            }
                            $record->objectifs()->create([
                                'uuid' => Str::uuid(),
                                'code' => $obj['code'],
                                'libelle_court' => $obj['libelle_court'] ?? '',
                                'heures' => $obj['heures'] ?? null,
                            ]);
                        }

                        Notification::make()
                            ->title('Savoir-faire mis à jour')
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Model $record) => $record->delete()),
            ]);
    }
}


