<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\RH\Models\LieuUnite;

class SuiviSavoirfairePage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'fcmcentral::filament.fcmcentral.pages.suivi.suivi-savoirfaire';
    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Suivi des Savoirfaire';
    protected static ?int $navigationSort = 7;

    public array $data = [];
    public ?string $selectedParcoursId = null;
    public ?string $selectedSavoirfaireUuid = null;
    public ?string $selectedStatut = null;
    public ?string $selectedLieuId = null;
    public string $activeTab = 'savoirfaire';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::suivi.savoirfaire');
    }

    public function mount(): void
    {
        $this->formParcours->fill();
        $this->formMarins->fill();
    }

    // ── Formulaire filtres ────────────────────────────────────────────────────

    public function formParcours(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('selectedParcoursId')
                    ->label('Parcours')
                    ->placeholder('Sélectionner un parcours…')
                    ->options(fn () => Parcours::orderBy('libelle_court')->pluck('libelle_court', 'id')->all())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedParcoursId = $state;
                        $this->selectedSavoirfaireUuid = null;
                        $this->data['selectedSavoirfaireUuid'] = null;
                        $this->resetTable();
                    }),
            ]);
    }

    public function formMarins(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(4)
            ->components([
                Select::make('selectedParcoursId')
                    ->label('Parcours')
                    ->placeholder('Sélectionner un parcours…')
                    ->options(fn () => Parcours::orderBy('libelle_court')->pluck('libelle_court', 'id')->all())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedParcoursId = $state;
                        $this->selectedSavoirfaireUuid = null;
                        $this->selectedStatut = null;
                        $this->data['selectedSavoirfaireUuid'] = null;
                        $this->data['selectedStatut'] = null;
                        $this->resetTable();
                    }),

                Select::make('selectedLieuId')
                    ->label('FPS')
                    ->placeholder('Tous les FPS')
                    ->options(fn () => LieuUnite::orderBy('libelle_court')->pluck('libelle_court', 'id')->all())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedLieuId = $state;
                        $this->resetTable();
                    }),

                Select::make('selectedSavoirfaireUuid')
                    ->label('Savoirfaire')
                    ->placeholder('Tous les savoirfaire')
                    ->options(fn () => $this->buildSavoirfaireOptions())
                    ->searchable()
                    ->live()
                    ->disabled(fn () => blank($this->selectedParcoursId))
                    ->helperText(fn () => blank($this->selectedParcoursId) ? 'Choisissez d\'abord un parcours' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedSavoirfaireUuid = $state;
                        $this->selectedStatut = null;
                        $this->data['selectedStatut'] = null;
                        $this->resetTable();
                    }),

                Select::make('selectedStatut')
                    ->label('Statut')
                    ->placeholder('Tous les statuts')
                    ->options([
                        'effectue'     => 'Effectué',
                        'propose'      => 'Proposé',
                        'non_effectue' => 'Non effectué',
                    ])
                    ->live()
                    ->disabled(fn () => blank($this->selectedSavoirfaireUuid))
                    ->helperText(fn () => blank($this->selectedSavoirfaireUuid) ? 'Choisissez d\'abord un savoirfaire' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedStatut = $state;
                        $this->resetTable();
                    }),
            ]);
    }

    public function buildSavoirfaireOptions(): array
    {
        if (blank($this->selectedParcoursId)) {
            return [];
        }

        $parcours = Parcours::with([
            'affectations.competences.competenceSavoirfaires.savoirfaire',
            'affectations.competences.competenceSavoirfaires.savoirfaireType',
        ])->find($this->selectedParcoursId);

        if (!$parcours) return [];

        $options = [];
        foreach ($parcours->affectations as $aff) {
            foreach ($aff->competences as $comp) {
                foreach ($comp->competenceSavoirfaires as $csf) {
                    if (!$csf->uuid) continue;
                    $options[$csf->uuid] = implode(' — ', array_filter([
                        $aff->code,
                        $csf->savoirfaireType?->code,
                        $csf->savoirfaire?->code,
                        $csf->libelle_court ?: $csf->savoirfaire?->libelle_court,
                    ]));
                }
            }
        }

        return $options;
    }

    // ── Navigation tabs ──────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectSavoirfaireAndShowMarins(string $uuid): void
    {
        $this->selectedSavoirfaireUuid = $uuid;
        $this->data['selectedSavoirfaireUuid'] = $uuid;
        $this->activeTab = 'marins';
        $this->resetTable();
    }

    public function clearSavoirfaireFilter(): void
    {
        $this->selectedSavoirfaireUuid = null;
        $this->selectedStatut = null;
        $this->data['selectedSavoirfaireUuid'] = null;
        $this->data['selectedStatut'] = null;
        $this->resetTable();
    }

    // ── Table marins ─────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->buildMarinsQuery())
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Exporter CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => $this->streamCsvResponse()),
            ])
            ->columns([
                TextColumn::make('fcmMarin.marin.grade.libelle_court')
                    ->label('Grade')
                    ->badge()
                    ->searchable(),

                TextColumn::make('fcmMarin.marin.nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fcmMarin.marin.prenom')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fcmMarin.marin.unite.libelle_court')
                    ->label('Unité')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('fcmMarin.cohorte.libelle_court')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->getStateUsing(fn (MarinParcours $record): string => $this->computeStatut($record))
                    ->color(fn (string $state): string => match ($state) {
                        'Effectué'    => 'success',
                        'Proposé'     => 'warning',
                        'Non effectué' => 'danger',
                        default       => 'gray',
                    }),
            ])
            ->emptyStateHeading('Aucun marin trouvé')
            ->emptyStateDescription(
                blank($this->selectedParcoursId)
                    ? 'Sélectionnez un parcours pour voir les marins.'
                    : 'Aucun marin ne possède ce savoirfaire dans son parcours.'
            )
            ->emptyStateIcon('heroicon-o-user-group')
            ->paginated([25, 50, 100]);
    }

    protected function buildMarinsQuery(): Builder
    {
        $query = MarinParcours::with([
            'fcmMarin.marin.grade',
            'fcmMarin.marin.unite',
            'fcmMarin.cohorte',
            'parcours_serialise',
        ])->whereNotNull('parcoursmarin')
            ->whereHas('fcmMarin', fn (Builder $q) => $q
                ->whereNotNull('date_sedimentation')
                ->whereNotNull('date_fcm')
                ->whereNull('date_competence'));

        if (blank($this->selectedParcoursId) && blank($this->selectedSavoirfaireUuid)) {
            return $query->whereRaw('1 = 0');
        }

        if (filled($this->selectedSavoirfaireUuid)) {
            $uuid = $this->selectedSavoirfaireUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].competenceSavoirfaires[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedSavoirfaireUuid)) {
            $matchingIds = $query->get(['id', 'parcoursmarin'])
                ->filter(fn (MarinParcours $mp) => $this->matchesStatut($mp))
                ->pluck('id')
                ->all();

            $query->whereIn('id', $matchingIds);
        }

        return $query;
    }

    protected function matchesStatut(MarinParcours $record): bool
    {
        if (blank($this->selectedStatut) || blank($this->selectedSavoirfaireUuid)) {
            return true;
        }

        $json = $record->parcoursmarin;
        if (!$json) return $this->selectedStatut === 'non_effectue';

        $csf = $this->findCsfByUuid($json, $this->selectedSavoirfaireUuid);
        if (!$csf) return $this->selectedStatut === 'non_effectue';

        return match ($this->selectedStatut) {
            'effectue'     => ($csf['etat_valid'] ?? false) === true,
            'propose'      => ($csf['etat_propos'] ?? false) === true && ($csf['etat_valid'] ?? false) !== true,
            'non_effectue' => ($csf['etat_valid'] ?? false) !== true && ($csf['etat_propos'] ?? false) !== true,
            default        => true,
        };
    }

    public function computeStatut(MarinParcours $record): string
    {
        $json = $record->parcoursmarin;
        if (!$json) return '—';

        if (filled($this->selectedSavoirfaireUuid)) {
            $csf = $this->findCsfByUuid($json, $this->selectedSavoirfaireUuid);
            if (!$csf) return '—';
            return match (true) {
                ($csf['etat_valid'] ?? false) === true  => 'Effectué',
                ($csf['etat_propos'] ?? false) === true => 'Proposé',
                default => 'Non effectué',
            };
        }

        $items = $this->extractSavoirfaires($json);
        $total = count($items);
        if ($total === 0) return '—';
        $effectues = count(array_filter($items, fn ($s) => ($s['etat_valid'] ?? false) === true));
        return "{$effectues}/{$total} savoirfaire";
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    protected function streamCsvResponse(): mixed
    {
        $query = MarinParcours::with([
            'fcmMarin.marin.grade',
            'fcmMarin.marin.specialite',
            'fcmMarin.marin.unite',
        ])->whereNotNull('parcoursmarin')
            ->whereHas('fcmMarin', fn (Builder $q) => $q
                ->whereNotNull('date_sedimentation')
                ->whereNotNull('date_fcm')
                ->whereNull('date_competence'));

        if (filled($this->selectedSavoirfaireUuid)) {
            $uuid = $this->selectedSavoirfaireUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].competenceSavoirfaires[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedSavoirfaireUuid)) {
            $matchingIds = $query->get(['id', 'parcoursmarin'])
                ->filter(fn (MarinParcours $mp) => $this->matchesStatut($mp))
                ->pluck('id')
                ->all();
            $query->whereIn('id', $matchingIds);
        }

        $records = $query->get();

        $statutLabel = match ($this->selectedStatut) {
            'effectue'     => 'effectues',
            'propose'      => 'proposes',
            'non_effectue' => 'non-effectues',
            default        => 'tous',
        };

        $filename = 'savoirfaire-marins-' . $statutLabel . '-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($records): void {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['GRADE', 'NOM', 'PRÉNOM', 'EMAIL', 'MATRICULE', 'UNITÉ', 'STATUT SAVOIRFAIRE'], ';');

            foreach ($records as $mp) {
                $marin = $mp->fcmMarin?->marin;
                if (!$marin) continue;

                fputcsv($file, [
                    $marin->grade?->libelle_court ?? '',
                    $marin->nom ?? '',
                    $marin->prenom ?? '',
                    $marin->email ?? '',
                    $marin->matricule ?? '',
                    $marin->unite?->libelle_long ?? '',
                    $this->computeStatut($mp),
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Stats onglet Savoirfaire ───────────────────────────────────────────────────

    public function getStatsParSavoirfaire(): array
    {
        if (blank($this->selectedParcoursId)) {
            return [];
        }

        $query = MarinParcours::whereNotNull('parcoursmarin')
            ->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));

        $stats = [];

        foreach ($query->get() as $mp) {
            $json = $mp->parcoursmarin;
            if (!$json) continue;

            foreach ($this->extractSavoirfaires($json) as $csf) {
                $uuid = $csf['uuid'] ?? null;
                if (!$uuid) continue;

                if (!isset($stats[$uuid])) {
                    $stats[$uuid] = [
                        'uuid'         => $uuid,
                        'libelle'      => implode(' — ', array_filter([
                            $csf['savoirfaire_type']['code'] ?? null,
                            $csf['savoirfaire']['code'] ?? null,
                            $csf['libelle_court'] ?? ($csf['savoirfaire']['libelle_court'] ?? null),
                        ])),
                        'total'        => 0,
                        'effectues'    => 0,
                        'proposes'     => 0,
                        'non_effectues' => 0,
                    ];
                }

                $stats[$uuid]['total']++;
                if (($csf['etat_valid'] ?? false) === true) {
                    $stats[$uuid]['effectues']++;
                } elseif (($csf['etat_propos'] ?? false) === true) {
                    $stats[$uuid]['proposes']++;
                } else {
                    $stats[$uuid]['non_effectues']++;
                }
            }
        }

        return array_values($stats);
    }

    // ── Helpers JSON ──────────────────────────────────────────────────────────

    protected function extractSavoirfaires(array $json): array
    {
        $items = [];
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    $items[] = $csf;
                }
            }
        }
        return $items;
    }

    protected function findCsfByUuid(array $json, string $uuid): ?array
    {
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    if (($csf['uuid'] ?? null) === $uuid) {
                        return $csf;
                    }
                }
            }
        }
        return null;
    }
}
