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

class SuiviCompetencesPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'fcmcentral::filament.fcmcentral.pages.suivi.suivi-competences';
    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Suivi des Compétences';
    protected static ?int $navigationSort = 8;

    public array $data = [];
    public ?string $selectedParcoursId = null;
    public ?string $selectedCompetenceUuid = null;
    public ?string $selectedStatut = null;
    public ?string $selectedLieuId = null;
    public string $activeTab = 'competences';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::suivi.competence');
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
                        $this->selectedCompetenceUuid = null;
                        $this->data['selectedCompetenceUuid'] = null;
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
                        $this->selectedCompetenceUuid = null;
                        $this->selectedStatut = null;
                        $this->data['selectedCompetenceUuid'] = null;
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

                Select::make('selectedCompetenceUuid')
                    ->label('Compétence')
                    ->placeholder('Toutes les compétences')
                    ->options(fn () => $this->buildCompetenceOptions())
                    ->searchable()
                    ->live()
                    ->disabled(fn () => blank($this->selectedParcoursId))
                    ->helperText(fn () => blank($this->selectedParcoursId) ? 'Choisissez d\'abord un parcours' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedCompetenceUuid = $state;
                        $this->selectedStatut = null;
                        $this->data['selectedStatut'] = null;
                        $this->resetTable();
                    }),

                Select::make('selectedStatut')
                    ->label('Statut')
                    ->placeholder('Tous les statuts')
                    ->options([
                        'effectue'     => 'Effectué',
                        'en_cours'     => 'En cours',
                        'non_effectue' => 'Non effectué',
                    ])
                    ->live()
                    ->disabled(fn () => blank($this->selectedCompetenceUuid))
                    ->helperText(fn () => blank($this->selectedCompetenceUuid) ? 'Choisissez d\'abord une compétence' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedStatut = $state;
                        $this->resetTable();
                    }),
            ]);
    }

    public function buildCompetenceOptions(): array
    {
        if (blank($this->selectedParcoursId)) {
            return [];
        }

        $parcours = Parcours::with(['affectations.competences'])->find($this->selectedParcoursId);

        if (!$parcours) return [];

        $options = [];
        foreach ($parcours->affectations as $aff) {
            foreach ($aff->competences as $comp) {
                if (!$comp->uuid) continue;
                $options[$comp->uuid] = implode(' — ', array_filter([
                    $aff->code,
                    $comp->code,
                    $comp->libelle_court,
                ]));
            }
        }

        return $options;
    }

    // ── Navigation tabs ──────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectCompetenceAndShowMarins(string $uuid): void
    {
        $this->selectedCompetenceUuid = $uuid;
        $this->data['selectedCompetenceUuid'] = $uuid;
        $this->activeTab = 'marins';
        $this->resetTable();
    }

    public function clearCompetenceFilter(): void
    {
        $this->selectedCompetenceUuid = null;
        $this->selectedStatut = null;
        $this->data['selectedCompetenceUuid'] = null;
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
                        'En cours'    => 'warning',
                        'Non effectué' => 'danger',
                        default       => 'gray',
                    }),
            ])
            ->emptyStateHeading('Aucun marin trouvé')
            ->emptyStateDescription(
                blank($this->selectedParcoursId)
                    ? 'Sélectionnez un parcours pour voir les marins.'
                    : 'Aucun marin ne possède cette compétence dans son parcours.'
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





        if (blank($this->selectedParcoursId) && blank($this->selectedCompetenceUuid)) {
            return $query->whereRaw('1 = 0');
        }

        if (filled($this->selectedCompetenceUuid)) {
            $uuid = $this->selectedCompetenceUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedCompetenceUuid)) {
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
        if (blank($this->selectedStatut) || blank($this->selectedCompetenceUuid)) {
            return true;
        }

        $json = $record->parcoursmarin;
        if (!$json) return $this->selectedStatut === 'non_effectue';

        $competence = $this->findCompetenceByUuid($json, $this->selectedCompetenceUuid);
        if (!$competence) return $this->selectedStatut === 'non_effectue';

        return $this->competenceStatutLabel($competence) === match ($this->selectedStatut) {
            'effectue'     => 'Effectué',
            'en_cours'     => 'En cours',
            'non_effectue' => 'Non effectué',
            default        => null,
        };
    }

    public function computeStatut(MarinParcours $record): string
    {
        $json = $record->parcoursmarin;
        if (!$json) return '—';

        if (filled($this->selectedCompetenceUuid)) {
            $competence = $this->findCompetenceByUuid($json, $this->selectedCompetenceUuid);
            if (!$competence) return '—';
            return $this->competenceStatutLabel($competence);
        }

        $competences = $this->extractCompetences($json);
        $total = count($competences);
        if ($total === 0) return '—';
        $effectuees = count(array_filter($competences, fn ($c) => $this->competenceStatutLabel($c) === 'Effectué'));
        return "{$effectuees}/{$total} compétences";
    }

    protected function competenceStatutLabel(array $competence): string
    {
        $csfs = $competence['competenceSavoirfaires'] ?? [];
        $total = count($csfs);
        if ($total === 0) return 'Non effectué';

        $valides = count(array_filter($csfs, fn ($csf) => ($csf['etat_valid'] ?? false) === true));

        return match (true) {
            $valides === $total => 'Effectué',
            $valides > 0        => 'En cours',
            default             => 'Non effectué',
        };
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


        if (filled($this->selectedCompetenceUuid)) {
            $uuid = $this->selectedCompetenceUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedCompetenceUuid)) {
            $matchingIds = $query->get(['id', 'parcoursmarin'])
                ->filter(fn (MarinParcours $mp) => $this->matchesStatut($mp))
                ->pluck('id')
                ->all();
            $query->whereIn('id', $matchingIds);
        }

        $records = $query->get();

        $statutLabel = match ($this->selectedStatut) {
            'effectue'     => 'effectuees',
            'en_cours'     => 'en-cours',
            'non_effectue' => 'non-effectuees',
            default        => 'toutes',
        };

        $filename = 'competences-marins-' . $statutLabel . '-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($records): void {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['GRADE', 'NOM', 'PRÉNOM', 'EMAIL', 'MATRICULE', 'UNITÉ', 'STATUT COMPÉTENCE'], ';');

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

    // ── Stats onglet Compétences ───────────────────────────────────────────────────

    public function getStatsParCompetence(): array
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

            foreach ($this->extractCompetences($json) as $competence) {
                $uuid = $competence['uuid'] ?? null;
                if (!$uuid) continue;

                if (!isset($stats[$uuid])) {
                    $stats[$uuid] = [
                        'uuid'         => $uuid,
                        'libelle'      => implode(' — ', array_filter([
                            $competence['code'] ?? null,
                            $competence['libelle_court'] ?? null,
                        ])),
                        'total'        => 0,
                        'effectues'    => 0,
                        'en_cours'     => 0,
                        'non_effectues' => 0,
                    ];
                }

                $stats[$uuid]['total']++;
                match ($this->competenceStatutLabel($competence)) {
                    'Effectué'      => $stats[$uuid]['effectues']++,
                    'En cours'      => $stats[$uuid]['en_cours']++,
                    default         => $stats[$uuid]['non_effectues']++,
                };
            }
        }

        return array_values($stats);
    }

    // ── Helpers JSON ──────────────────────────────────────────────────────────

    protected function extractCompetences(array $json): array
    {
        $competences = [];
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                $competences[] = $comp;
            }
        }
        return $competences;
    }

    protected function findCompetenceByUuid(array $json, string $uuid): ?array
    {
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                if (($comp['uuid'] ?? null) === $uuid) {
                    return $comp;
                }
            }
        }
        return null;
    }
}
