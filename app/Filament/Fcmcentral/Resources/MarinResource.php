<?php
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\AffecterMentorAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\GelAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\AttribuerParcoursAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\CreerUneNoteAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\DownloadCsvAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\JalonFcmAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\LivretPdfAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions\ToggleFcmAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Filters\EnFcmFilter;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCommun\Models\Cohorte;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Specialite;
use Modules\RH\Models\Unite;
class MarinResource extends Resource
{
    protected static ?string $model = Marin::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = 'Marins';
    public static function canCreate(): bool
    {
        return false;
        static::canAccess();
    }

    /**
     * Badge de navigation avec comptage selon les droits.
     */
    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->can('fcmcentral::voir_tous_les_marins')) {
            return (string) Marin::enFcm()->count();
        }
        $currentMarin = Marin::fromCurrentUser();
        if ($currentMarin) {
            return FcmMarin::whereHas('mentors', fn($q) => $q->where('marin_id', $currentMarin->id))->count();
        }
        return '0';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nom')->required()->maxLength(1500),
            TextInput::make('prenom')->required()->maxLength(1000),
            TextInput::make('email')->email()->required()->unique(table: 'rh_marins', column: 'email', ignoreRecord: true),
            TextInput::make('nid')->maxLength(15)->required(),
            TextInput::make('matricule')->maxLength(20),
            Select::make('grade_id')->relationship('grade', 'libelle_long'),
            Select::make('specialite_id')->relationship('specialite', 'libelle_court'),
            Select::make('brevet_id')->relationship('brevet', 'libelle_long'),
            Select::make('unite_id')->relationship('unite', 'libelle_long'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nom', 'asc')
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([25, 50, 100])
            ->headerActions([
                // Telechargement CSV
                DownloadCsvAction::make('downloadCsv')
                    ->visible(fn() => auth()->check() && auth()->user()->can('fcmcentral::exporter_csv')),
                // Fin Telechargment
                Action::make('recherche-annuaire')
                    ->label('Ajouter un marin')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->button()
                    ->url(fn() => RechercheAnnuairePage::getUrl())
                    ->visible(fn() => RechercheAnnuairePage::canAccess()),
            ])
            ->columns([
                TextColumn::make('grade.libelle_court')->searchable(),
                TextColumn::make('unite.libelle_court')->searchable()->toggleable(),
                TextColumn::make('unite.lieuUnite.libelle_court')->label('FPS')->searchable()->toggleable(),
                TextColumn::make('nom')->searchable()->sortable(),
                TextColumn::make('prenom')->searchable()->label('Prénom'),
                // 🎯 FCM ACTIF (Oui/Non)
                // Colonne FCM avec gestion du typeEtat
                Tables\Columns\IconColumn::make('fcm_actif')
                    ->label('FCM')
                    ->boolean()
                    ->state(fn($record) => $record->suivi_en_fcm && 'FIN' !== $record->complementsFcm?->getJalonFcmLibelle())
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon(fn($record) => 'FIN' === $record->complementsFcm?->getJalonFcmLibelle() ? 'heroicon-c-shield-check' : 'heroicon-o-x-circle')
                    ->trueColor(fn($record) => 'FIN' === $record->complementsFcm?->getJalonFcmLibelle() ? 'gray' : 'success')
                    ->falseColor(fn($record) => 'FIN' === $record->complementsFcm?->getJalonFcmLibelle() ? 'gray' : 'danger')
                    ->hidden(fn($livewire): bool => method_exists($livewire, 'isFilterEnFcmActive') && $livewire->isFilterEnFcmActive())
                    ->action(
                        ToggleFcmAction::make('toggle_fcm')
                            ->visible(
                                fn($record) => 'FIN' !== $record->complementsFcm?->getJalonFcmLibelle()
                                    && (
                                        auth()->user()->can('fcmcentral::fcm.admin')
                                        || auth()->user()->canAny([
                                            'fcmcentral::attribuer.cohorte',
                                            'fcmcentral::attribuer.mentor',
                                            'fcmcentral::attribuer.parcours',
                                        ])
                                    )
                            )
                    ),
                // Colonne Type Etat FCM dynamique
                TextColumn::make('type_jalon_fcm')
                    ->label('Jalon')
                    ->state(function ($record) {
                        if (!$record->suivi_en_fcm) {
                            return null;
                        }
                        return $record->complementsFcm?->getJalonFcmLibelle();
                    })
                    ->badge()
                    ->color(function ($record) {
                        $etat = $record->complementsFcm?->getJalonFcmLibelle();
                        return match ($etat) {
                            'SED' => 'info',
                            'FCM' => 'success',
                            'FIN' => 'gray',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($record) {
                        $etat = $record->complementsFcm?->getJalonFcmLibelle();
                        return match ($etat) {
                            'SED' => 'heroicon-s-arrow-path',
                            'FCM' => 'heroicon-s-check-circle',
                            'FIN' => 'heroicon-o-minus-circle',
                            default => 'heroicon-o-minus-circle',
                        };
                    })
                    ->placeholder('Non assigné')
                    ->tooltip('Cliquer pour modifier')
                    ->action(
                        JalonFcmAction::make('jalons_fcm')
                            ->visible(
                                fn($record) => $record->suivi_en_fcm
                                    && auth()->check()
                                    && auth()->user()->can('fcmcentral::definir_jalons_de_fcm')
                            )
                    ),
                TextColumn::make('complementsFcm.cohorte.libelle_court')
                    ->label('Cohorte')
                    // ->searchable()
                    ->toggleable()
                    ->placeholder('Non assignée'),
                TextColumn::make('mentors_affichage')
                    ->label('Mentor(s)')
                    ->state(function ($record) {
                        $mentors = $record->complementsFcm?->mentors ?? collect();
                        if ($mentors->isEmpty()) {
                            return 'Non assigné';
                        }
                        return $mentors->map(fn($m) => trim(($m->grade?->libelle_court ?? '') . ' ' . $m->nom))->join(', ');
                    })
                    ->badge()
                    ->color(function ($record) {
                        $mentors = $record->complementsFcm?->mentors ?? collect();
                        return $mentors->isNotEmpty() ? 'success' : 'gray';
                    })
                    ->icon(function ($record) {
                        $mentors = $record->complementsFcm?->mentors ?? collect();
                        return $mentors->isNotEmpty() ? 'heroicon-s-user-circle' : 'heroicon-o-user-circle';
                    })
                    ->action(
                        AffecterMentorAction::make('gerer_mentor')
                            ->visible(
                                fn($record) => $record->estEnFcm()
                                    && auth()->check()
                                    && auth()->user()->can('fcmcentral::attribuer.mentor')
                            )
                    ),
                // 📚 PARCOURS CLIQUABLE
                TextColumn::make('complementsFcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label('Parcours')
                    ->formatStateUsing(
                        fn($record) => $record->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné'
                    )
                    ->badge()
                    ->color(fn($record) => $record->complementsFcm?->donnees_du_parcours ? 'success' : 'gray')
                    ->icon(fn($record) => $record->complementsFcm?->donnees_du_parcours ? 'heroicon-s-book-open' : 'heroicon-s-plus')
                    ->placeholder('Non assigné')
                    ->sortable(query: function ($query, string $direction) {
                        $query->leftJoin('fcmcentral_fcm_marins', 'fcmcentral_fcm_marins.rh_marin_id', '=', 'rh_marins.id')
                            ->leftJoin('fcmcentral_marin_parcours', 'fcmcentral_marin_parcours.fcmmarin_id', '=', 'fcmcentral_fcm_marins.id')
                            ->leftJoin('fcmcentral_parcours_serialises', 'fcmcentral_parcours_serialises.id', '=', 'fcmcentral_marin_parcours.parcoursserialise_id')
                            ->orderBy('fcmcentral_parcours_serialises.libelle_court', $direction)
                            ->select('rh_marins.*');
                    })
                    ->action(
                        AttribuerParcoursAction::make('gerer_parcours')
                            ->visible(
                                fn($record) => $record->estEnFcm()
                                    && auth()->check()
                                    && auth()->user()->can('fcmcentral::attribuer.parcours')
                            )
                    ),
                TextColumn::make('complementsFcm.donnees_du_parcours.taux_global')
                    ->label('Taux')
                    ->suffix('%')
                    ->formatStateUsing(fn($state) => intval(round($state)))
                    ->placeholder('0')
                    ->sortable(query: function ($query, string $direction) {
                        $query->leftJoin('fcmcentral_fcm_marins as fcm_sort', 'fcm_sort.rh_marin_id', '=', 'rh_marins.id')
                            ->leftJoin('fcmcentral_marin_parcours as mp_sort', 'mp_sort.fcmmarin_id', '=', 'fcm_sort.id')
                            ->orderBy('mp_sort.taux_global', $direction)
                            ->select('rh_marins.*');
                    }),
                Tables\Columns\IconColumn::make('gel')
                    ->label('Gel')
                    ->state(fn($record) => $record->estGele())
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable()
                    ->action(
                        GelAction::make('gerer_gel')
                            ->visible(fn($record) => $record->suivi_en_fcm
                                && auth()->check()
                                && auth()->user()->can('fcmcentral::definir_jalons_de_fcm')
                            )
                    ),
                // 🔔 ACTIVITÉS EN ATTENTE
                TextColumn::make('activites_en_attente')
                    ->label('En attente')
                    ->state(fn($record) => static::getActivitesEnAttenteCount($record))
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'warning' : 'success')
                    ->icon(fn(int $state): string => $state > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                    ->extraAttributes(['class' => 'cursor-default']),
                // ->tooltip(fn (int $state): string => $state > 0 ? "$state activité(s) en attente de validation" : 'Aucune activité en attente'),
            ])
            ->filters([
                EnFcmFilter::make('en_fcm'),
                SelectFilter::make('specialite_id')
                    ->label('Spécialité')
                    ->options(Specialite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),
                SelectFilter::make('unite_id')
                    ->label('Unité')
                    ->options(Unite::query()->orderBy('libelle_long')->pluck('libelle_long', 'id')),
                SelectFilter::make('lieu_unite')
                    ->label('Lieu')
                    ->options(LieuUnite::query()->orderBy('libelle_long')->pluck('libelle_long', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas(
                            'unite',
                            fn(Builder $q) => $q->where('lieu_unite_id', $data['value'])
                        );
                    }),
                SelectFilter::make('jalon')
                    ->label('Jalon')
                    ->options(['SED' => 'SED', 'FCM' => 'FCM', 'FIN' => 'FIN'])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return match ($data['value']) {
                            'SED' => $query->whereHas('complementsFcm', fn($q) => $q->whereNotNull('date_sedimentation')->whereNull('date_fcm')->whereNull('date_competence')),
                            'FCM' => $query->whereHas('complementsFcm', fn($q) => $q->whereNotNull('date_sedimentation')->whereNotNull('date_fcm')->whereNull('date_competence')),
                            'FIN' => $query->whereHas('complementsFcm', fn($q) => $q->whereNotNull('date_sedimentation')->whereNotNull('date_fcm')->whereNotNull('date_competence')),
                            default => $query,
                        };
                    }),
                SelectFilter::make('cohorte')
                    ->label('Cohorte')
                    ->options(Cohorte::query()->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('complementsFcm', fn($q) => $q->where('cohorte_id', $data['value']));
                    }),
                SelectFilter::make('parcours')
                    ->label('Parcours')
                    ->options(Parcours::query()->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas(
                            'complementsFcm.lignesDansMarinParcours.parcours_serialise',
                            fn($q) => $q->where('parcours_id', $data['value'])
                        );
                    }),
                SelectFilter::make('gel')
                    ->label('Gel')
                    ->options(['oui' => 'En gel', 'non' => 'Non gelé'])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $data['value'] === 'oui'
                            ? $query->whereHas('etats', fn($q) => $q->whereNull('date_fin'))
                            : $query->whereDoesntHave('etats', fn($q) => $q->whereNull('date_fin'));
                    }),
                SelectFilter::make('en_attente')
                    ->label('En attente')
                    ->options(['oui' => 'En attente', 'non' => 'Non en attente'])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $wantsAttente = $data['value'] === 'oui';
                        $ids = (clone $query)
                            ->with(['complementsFcm.lignesDansMarinParcours'])
                            ->get()
                            ->filter(fn($m) => $wantsAttente
                                ? static::getActivitesEnAttenteCount($m) > 0
                                : static::getActivitesEnAttenteCount($m) === 0
                            )
                            ->pluck('id');

                        return $query->whereIn('rh_marins.id', $ids);
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            // ICONES DROITE
            ->recordActions([
                // EDIT
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn($record) => static::canEdit($record)),
                // VOIR LIVRET
                Action::make('livret-de-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-book-open')
                    ->color('info')
                    ->tooltip('Livret FCM')
                    ->extraAttributes([
                        'title' => 'Livret FCM',
                        'class' => 'btn-livret',
                    ])
                    ->visible(fn($record) => LivretDeFcmLight::canAccess(['record' => $record]))
                    ->url(fn($record) => LivretDeFcmLight::getUrl(['record' => $record->id])),
                Action::make('mon-livret-de-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-book-open')
                    ->color('info')
                    ->tooltip('Livret FCM')
                    ->extraAttributes([
                        'title' => 'Livret FCM',
                        'class' => 'btn-livret',
                    ])
                    ->visible(fn($record) => $record->id == Marin::fromCurrentUser()?->id && MonLivretDeFcm::canAccess())
                    ->url(fn($record) => MonLivretDeFcm::getUrl()),
                Action::make('fiche-bilan')
                    ->iconButton()
                    ->icon('heroicon-m-chart-bar-square')
                    ->color('warning')
                    ->extraAttributes([
                        'title' => 'Fiche Bilan FCM',
                        'class' => 'btn-bilan',
                    ])
                    ->visible(fn($record) => FicheBilan::canAccess(['record' => $record]))
                    ->url(fn($record) => FicheBilan::getUrl(['record' => $record->id])),
                LivretPdfAction::make('livret-pdf')
                    ->visible(fn($record) => $record->complementsFcm?->donnees_du_parcours
                        && auth()->check()
                        && auth()->user()->can('fcmcentral::livret.telecharger')),
                CreerUneNoteAction::make('create-note')
                    ->visible(
                        fn($record) => $record->suivi_en_fcm
                            && auth()->check()
                            && auth()->user()->can('fcmcentral::note.creer')
                    ),
            ])
        ;
    }

    /**
     * Compte les activités en attente de validation (objectifs + savoir-faires).
     *
     * @param mixed $marin
     */
    public static function getActivitesEnAttenteCount($marin): int
    {
        try {
            if (!$marin->suiviEnFcm || !$marin->complementsFcm) {
                return 0;
            }
            $parcours = $marin->complementsFcm->donnees_du_parcours->parcoursmarin ?? null;
            if (!$parcours) {
                return 0;
            }
            $pendingCount = 0;
            foreach ($parcours['affectations'] ?? [] as $affectation) {
                foreach ($affectation['competences'] ?? [] as $competence) {
                    foreach ($competence['competenceSavoirfaires'] ?? [] as $savoirfaire) {
                        // Vérifier si le savoir-faire est proposé
                        $sfPropose = !empty($savoirfaire['date_proposition']);
                        $sfValide = ($savoirfaire['etat_valid'] ?? false) === true;
                        if ($sfPropose && !$sfValide) {
                            ++$pendingCount;
                        }
                        // Vérifier les objectifs
                        foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                            $objPropose = !empty($objectif['date_proposition']);
                            $objValide = ($objectif['etat_valid'] ?? false) === true;
                            if ($objPropose && !$objValide) {
                                // if ($objPropose ) {
                                ++$pendingCount;
                            }
                        }
                    }
                }
            }
            return $pendingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarins::route('/'),
            'livret-de-fcm' => LivretDeFcmLight::route('/{record}/livret-de-fcm-light'),
            'fiche-bilan' => FicheBilan::route('/{record}/fiche-bilan'),
        ];
    }
    
}
