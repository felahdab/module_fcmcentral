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

class SuiviObjectifsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'fcmcentral::filament.fcmcentral.pages.suivi.suivi-objectifs';
    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Suivi des Objectifs';
    protected static ?int $navigationSort = 9;

    public array $data = [];
    public ?string $selectedParcoursId = null;
    public ?string $selectedObjectifUuid = null;
    public ?string $selectedStatut = null;
    public ?string $selectedLieuId = null;
    public string $activeTab = 'objectifs';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::suivi.objectif');
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
                        $this->selectedObjectifUuid = null;
                        $this->data['selectedObjectifUuid'] = null;
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
                        $this->selectedObjectifUuid = null;
                        $this->selectedStatut = null;
                        $this->data['selectedObjectifUuid'] = null;
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

                Select::make('selectedObjectifUuid')
                    ->label('Objectif')
                    ->placeholder('Tous les objectifs')
                    ->options(fn () => $this->buildObjectifOptions())
                    ->searchable()
                    ->live()
                    ->disabled(fn () => blank($this->selectedParcoursId))
                    ->helperText(fn () => blank($this->selectedParcoursId) ? 'Choisissez d\'abord un parcours' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedObjectifUuid = $state;
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
                    ->disabled(fn () => blank($this->selectedObjectifUuid))
                    ->helperText(fn () => blank($this->selectedObjectifUuid) ? 'Choisissez d\'abord un objectif' : null)
                    ->afterStateUpdated(function (mixed $state): void {
                        $this->selectedStatut = $state;
                        $this->resetTable();
                    }),
            ]);
    }

    public function buildObjectifOptions(): array
    {
        if (blank($this->selectedParcoursId)) {
            return [];
        }

        $parcours = Parcours::with([
            'affectations.competences.competenceSavoirfaires.savoirfaire',
            'affectations.competences.competenceSavoirfaires.objectifs',
        ])->find($this->selectedParcoursId);

        if (!$parcours) return [];

        $options = [];
        foreach ($parcours->affectations as $aff) {
            foreach ($aff->competences as $comp) {
                foreach ($comp->competenceSavoirfaires as $csf) {
                    foreach ($csf->objectifs as $objectif) {
                        if (!$objectif->uuid) continue;
                        $options[$objectif->uuid] = implode(' — ', array_filter([
                            $aff->code,
                            $csf->savoirfaire?->code,
                            $objectif->code,
                            $objectif->libelle_court ?: $objectif->libelle_long,
                        ]));
                    }
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

    public function selectObjectifAndShowMarins(string $uuid): void
    {
        $this->selectedObjectifUuid = $uuid;
        $this->data['selectedObjectifUuid'] = $uuid;
        $this->activeTab = 'marins';
        $this->resetTable();
    }

    public function clearObjectifFilter(): void
    {
        $this->selectedObjectifUuid = null;
        $this->selectedStatut = null;
        $this->data['selectedObjectifUuid'] = null;
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
                    : 'Aucun marin ne possède cet objectif dans son parcours.'
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

        if (blank($this->selectedParcoursId) && blank($this->selectedObjectifUuid)) {
            return $query->whereRaw('1 = 0');
        }

        if (filled($this->selectedObjectifUuid)) {
            $uuid = $this->selectedObjectifUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].competenceSavoirfaires[*].objectifs[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedObjectifUuid)) {
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
        if (blank($this->selectedStatut) || blank($this->selectedObjectifUuid)) {
            return true;
        }

        $json = $record->parcoursmarin;
        if (!$json) return $this->selectedStatut === 'non_effectue';

        $objectif = $this->findObjectifByUuid($json, $this->selectedObjectifUuid);
        if (!$objectif) return $this->selectedStatut === 'non_effectue';

        return match ($this->selectedStatut) {
            'effectue'     => ($objectif['etat_valid'] ?? false) === true,
            'propose'      => ($objectif['etat_propos'] ?? false) === true && ($objectif['etat_valid'] ?? false) !== true,
            'non_effectue' => ($objectif['etat_valid'] ?? false) !== true && ($objectif['etat_propos'] ?? false) !== true,
            default        => true,
        };
    }

    public function computeStatut(MarinParcours $record): string
    {
        $json = $record->parcoursmarin;
        if (!$json) return '—';

        if (filled($this->selectedObjectifUuid)) {
            $objectif = $this->findObjectifByUuid($json, $this->selectedObjectifUuid);
            if (!$objectif) return '—';
            return match (true) {
                ($objectif['etat_valid'] ?? false) === true  => 'Effectué',
                ($objectif['etat_propos'] ?? false) === true => 'Proposé',
                default => 'Non effectué',
            };
        }

        $objectifs = $this->extractObjectifs($json);
        $total = count($objectifs);
        if ($total === 0) return '—';
        $effectues = count(array_filter($objectifs, fn ($o) => ($o['etat_valid'] ?? false) === true));
        return "{$effectues}/{$total} objectifs";
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

        if (filled($this->selectedObjectifUuid)) {
            $uuid = $this->selectedObjectifUuid;
            $query->whereRaw(
                "JSON_SEARCH(parcoursmarin, 'one', ?, null, '$.affectations[*].competences[*].competenceSavoirfaires[*].objectifs[*].uuid') IS NOT NULL",
                [$uuid]
            );
        } elseif (filled($this->selectedParcoursId)) {
            $query->whereHas('parcours_serialise', fn (Builder $q) => $q->where('parcours_id', $this->selectedParcoursId));
        }

        if (filled($this->selectedLieuId)) {
            $query->whereHas('fcmMarin.marin.unite.lieuUnite', fn (Builder $q) => $q->where('id', $this->selectedLieuId));
        }

        if (filled($this->selectedStatut) && filled($this->selectedObjectifUuid)) {
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

        $filename = 'objectifs-marins-' . $statutLabel . '-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($records): void {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['GRADE', 'NOM', 'PRÉNOM', 'EMAIL', 'MATRICULE', 'UNITÉ', 'STATUT OBJECTIF'], ';');

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

    // ── Stats onglet Objectifs ───────────────────────────────────────────────────

    public function getStatsParObjectif(): array
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

            foreach ($this->extractObjectifs($json) as $objectif) {
                $uuid = $objectif['uuid'] ?? null;
                if (!$uuid) continue;

                if (!isset($stats[$uuid])) {
                    $stats[$uuid] = [
                        'uuid'         => $uuid,
                        'libelle'      => implode(' — ', array_filter([
                            $objectif['code'] ?? null,
                            $objectif['libelle_court'] ?? ($objectif['libelle_long'] ?? null),
                        ])),
                        'total'        => 0,
                        'effectues'    => 0,
                        'proposes'     => 0,
                        'non_effectues' => 0,
                    ];
                }

                $stats[$uuid]['total']++;
                if (($objectif['etat_valid'] ?? false) === true) {
                    $stats[$uuid]['effectues']++;
                } elseif (($objectif['etat_propos'] ?? false) === true) {
                    $stats[$uuid]['proposes']++;
                } else {
                    $stats[$uuid]['non_effectues']++;
                }
            }
        }

        return array_values($stats);
    }

    // ── Helpers JSON ──────────────────────────────────────────────────────────

    protected function extractObjectifs(array $json): array
    {
        $objectifs = [];
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $objectifs[] = $objectif;
                    }
                }
            }
        }
        return $objectifs;
    }

    protected function findObjectifByUuid(array $json, string $uuid): ?array
    {
        foreach ($json['affectations'] ?? [] as $aff) {
            foreach ($aff['competences'] ?? [] as $comp) {
                foreach ($comp['competenceSavoirfaires'] ?? [] as $csf) {
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        if (($objectif['uuid'] ?? null) === $uuid) {
                            return $objectif;
                        }
                    }
                }
            }
        }
        return null;
    }
}
