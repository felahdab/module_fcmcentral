<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use App\Filament\Pages\RechercheAnnuairePage;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\FcmMarin;

use Modules\FcmCommun\Services\EventTriggerService;
use Modules\RH\Models\Specialite;
use Modules\RH\Models\Unite;
use Modules\FcmCentral\Traits\FcmAccessControl;

class MarinResource extends Resource
{
    
    use FcmAccessControl;
    
    protected static ?string $model = Marin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = "Marins";

    /**
     * Contrôle d'accès global à la resource
     */
    public static function canAccess(array $parameters = []): bool
    {
        // Admins ET mentors peuvent accéder
        return static::canAccessAdminMenus() || static::canAccessMentorFeatures();
    }

    public static function canViewAny(): bool
    {
        return static::canAccessAdminMenus() || static::canAccessMentorFeatures();
    }

    public static function canCreate(): bool
    {
        return false; // Pas de création directe
    }

    public static function canEdit($record): bool
    {
        // Admins peuvent tout éditer, mentors seulement leurs marins
        if (static::canAccessAdminMenus()) {
            return true;
        }
        
        if (static::canAccessMentorFeatures()) {
            return static::isMentorOfMarin($record);
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        return static::canAccessAdminMenus(); // Seuls les admins peuvent supprimer
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccessAdminMenus();
    }

    /**
     * Vérifie si l'utilisateur connecté est mentor du marin donné
     */
    public static function isMentorOfMarin($marin): bool
    {
        try {
            $currentMarin = Marin::fromCurrentUser();
            if (!$currentMarin) {
                return false;
            }

            return FcmMarin::where('rh_marin_id', $marin->id)
                ->where('mentor_id', $currentMarin->id)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Badge de navigation avec comptage selon les droits
     */
    public static function getNavigationBadge(): ?string
    {
        try {
            if (static::canAccessAdminMenus()) {
                // Admins voient tous les marins FCM
                return (string) Marin::enFcm()->count();
            }
            
            if (static::canAccessMentorFeatures()) {
                // Mentors voient seulement leurs marins
                $currentMarin = Marin::fromCurrentUser();
                if ($currentMarin) {
                    $count = FcmMarin::where('mentor_id', $currentMarin->id)->count();
                    return $count > 0 ? (string) $count : null;
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, pas de badge
        }
        
        return null;
    }

    /**
     * Filtrage automatique selon les droits
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        try {
            // Les admins voient tout
            if (static::canAccessAdminMenus()) {
                return $query;
            }
            
            // Les mentors voient seulement leurs marins
            if (static::canAccessMentorFeatures()) {
                $currentMarin = Marin::fromCurrentUser();
                if ($currentMarin) {
                    $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
                        ->pluck('rh_marin_id');
                    
                    return $query->whereIn('id', $marinIds);
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, query vide
            return $query->whereRaw('1 = 0');
        }

        // Par défaut, query vide
        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->autofocus()
                    ->maxLength(1500)
                    ->label('Nom'),
                Forms\Components\Select::make('grade_id')
                    ->relationship(name: 'grade', titleAttribute: 'libelle_long')
                    ->label('Grade'),
                Forms\Components\TextInput::make('prenom')
                    ->required()
                    ->maxLength(1000)
                    ->label('Prénom'),
                Forms\Components\Select::make('specialite_id')
                    ->relationship(name: 'specialite', titleAttribute: 'libelle_court')
                    ->label('Spécialité'),
                Forms\Components\TextInput::make('email')
                    ->unique(
                        table: 'rh_marins',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->required()
                    ->label('Email'),
                Forms\Components\Select::make('brevet_id')
                    ->relationship(name: 'brevet', titleAttribute: 'libelle_long')
                    ->label('Brevet'),
                Forms\Components\TextInput::make('nid')
                    ->maxLength(15)
                    ->required()
                    ->default('')
                    ->label('NID'),
                Forms\Components\Select::make('unite_id')
                    ->relationship(name: 'unite', titleAttribute: 'libelle_long')
                    ->label('Unité'),
                Forms\Components\TextInput::make('matricule')
                    ->maxLength(20)
                    ->default('')
                    ->label('Matricule'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nom', 'asc')
            ->headerActions([
                Tables\Actions\Action::make('recherche-annuaire')
                ->label('Ajouter un marin depuis l\'annuaire')
                ->icon ('heroicon-o-user-plus')
                ->color('success')
                ->size('lg')
                ->button()
                ->extraAttributes([
                    'class'=>'font-semibold shadow-lg',
                    'style'=>'background:linear-gradient(135deg, #059669 0%, #047857 100%);'
                ])
                ->url(fn()=> RechercheAnnuairePage::getUrl())
                ->visible(fn()=>static::canAccessAdminMenus()||static::canAccessMentorFeatures())
                ->tooltip('Recherche un marin dans l\'annuaire pour ajouter dans  au suivi FCM')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->hidden(true),
                Tables\Columns\TextColumn::make('grade.libelle_court')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->searchable()
                    ->label('Prénom'),
                Tables\Columns\IconColumn::make('suiviEnFcm')
                    ->boolean()
                    ->label('Suivi FCM'),
                Tables\Columns\TextColumn::make('complements_fcm.cohorte.libelle_court')
                    ->label('Cohorte')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.mentor.nom')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label("Parcours attribué")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->label("Taux global")
                    ->searchable()
                    ->sortable()
                    ->suffix('%'),
                // Nouvelle colonne pour les activités en attente
                Tables\Columns\TextColumn::make('activites_en_attente')
                    ->label('En attente')
                    ->state(function ($record) {
                        return static::getActivitesEnAttenteCount($record);
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state > 0 ? 'warning' : 'success')
                    ->icon(fn (string $state): string => $state > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle'),
                Tables\Columns\TextColumn::make('matricule')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('nid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialite.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                Tables\Columns\TextColumn::make('brevet.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                Tables\Columns\TextColumn::make('unite.libelle_court')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
                Filter::make('en_fcm')
                    ->form([
                        Toggle::make('en_fcm')
                            ->default(true),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                array_key_exists('en_fcm', $data) && $data['en_fcm'],
                                fn (Builder $query, $date): Builder => $query->enFcm(),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! array_key_exists('en_fcm', $data) || ! $data['en_fcm']) {
                            return null;
                        }
                        return 'Marins en FCM seulement';
                    }),
                
                // Filtre spécial pour les activités en attente
                Filter::make('activites_en_attente')
                    ->form([
                        Toggle::make('has_pending')
                            ->label('Avec activités en attente'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!($data['has_pending'] ?? false)) {
                            return $query;
                        }
                        
                        return $query->whereHas('complements_fcm', function ($q) {
                            $q->whereNotNull('donnees_du_parcours');
                        })->get()->filter(function ($marin) {
                            return static::getActivitesEnAttenteCount($marin) > 0;
                        })->pluck('id')->pipe(function ($ids) use ($query) {
                            return $query->whereIn('id', $ids);
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!($data['has_pending'] ?? false)) {
                            return null;
                        }
                        return 'Avec activités en attente';
                    }),

                SelectFilter::make('specialite_id')
                    ->label('Spécialité')
                    ->options(Specialite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),
                SelectFilter::make('unite_id')
                    ->label('Unité')
                    ->options(Unite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->extraAttributes([
                        'title' => 'Modifier',
                        'class' => 'btn-modif',
                    ])
                    ->visible(fn ($record) => static::canEdit($record)),

                Tables\Actions\Action::make("suivre_en_fcm")
                    ->iconButton()
                    ->icon('heroicon-m-folder-plus')
                    ->extraAttributes([
                        'title' => 'Suivre en FCM',
                        'class' => 'btn-suivifcm',
                    ])
                    ->hidden(function ($record) {
                        return $record->suiviEnFcm;
                    })
                    ->visible(fn ($record) => static::canAccessAdminMenus()) // Seuls les admins
                    ->action(function ($record) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::MARIN_EN_FCM,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $record->uuid,
                            []
                        );
                        event($event);
                    }),

                Tables\Actions\Action::make('livret-de-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-book-open')
                    ->extraAttributes([
                        'title' => 'Livret FCM',
                        'class' => 'btn-livret',
                    ])
                    ->visible(function ($record) {
                        return $record->suiviEnFcm &&
                                $record->complements_fcm &&
                                $record->complements_fcm->parcoursSerialises()->exists() &&
                                (static::canAccessAdminMenus() || static::isMentorOfMarin($record));
                    })
                    ->action(function ($record){
                        $url = static::getUrl('livret-de-fcm',['record' => $record->id]);
                        return redirect($url);
                    }),

                Tables\Actions\Action::make('livret-pdf')
                    ->iconButton()
                    ->icon('heroicon-m-document-text')
                    ->extraAttributes([
                        'title' => 'Télécharger PDF du Livret FCM',
                        'class' => 'btn-pdf',
                    ])
                    ->visible(function ($record) {
                        return $record->suiviEnFcm &&
                                $record->complements_fcm &&
                                $record->complements_fcm->parcoursSerialises()->exists() &&
                                (static::canAccessAdminMenus() || static::isMentorOfMarin($record));
                    })
                    ->action(function ($record) {
                        return response()->streamDownload(function () use ($record) {
                            echo app(\Modules\FcmCommun\Services\LivretPdfService::class)->generatePdf($record);
                        }, "livret-fcm-{$record->nom}-{$record->prenom}.pdf", [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),

                Tables\Actions\Action::make("ne_plus_suivre_en_fcm")
                    ->iconButton()
                    ->icon('heroicon-m-folder-minus')
                    ->extraAttributes([
                        'title' => 'Ne plus suivre en FCM',
                        'class' => 'btn-suivifcm',
                    ])
                    ->color('danger')
                    ->visible(function ($record) {
                        return $record->suiviEnFcm && static::canAccessAdminMenus(); // Seuls les admins
                    })
                    ->action(function ($record) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::MARIN_PAS_FCM,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $record->uuid,
                            []
                        );
                        event($event);
                    }),

                Tables\Actions\Action::make('attributs-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-swatch')
                    ->modalHeading('Attributs FCM')
                    ->modalDescription('Selectionnez la cohorte, le mentor et le parcours à attribuer au marin.')
                    ->extraAttributes([
                        'title' => 'Attribuer cohorte - mentor - parcours',
                        'class' => 'btn-suivifcm',
                    ])
                    ->visible(fn ($record) => $record->suiviEnFcm && static::canAccessAdminMenus()) // Seuls les admins
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('cohorte_id')
                            ->label("Choisir la cohorte")
                            ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),

                        Forms\Components\Select::make('mentor_id')
                            ->label("Choisir le mentor")
                            ->options(function () {
                                return Marin::query()
                                    ->whereNull('deleted_at')
                                    ->orderBy('nom')
                                    ->orderBy('prenom')
                                    ->get()
                                    ->mapWithKeys(function ($marin) {
                                        $fullName = trim($marin->prenom . ' ' . $marin->nom);
                                        $grade = $marin->grade?->libelle_court ?? '';
                                        $label = $grade ? $grade . ' ' . $fullName : $fullName;
                                        return [$marin->uuid => $label];
                                    });
                            })
                            ->searchable()
                            ->placeholder('Sélectionnez un mentor'),

                        Forms\Components\Select::make('parcours_id')
                            ->label('Choisir un Parcours')
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable()
                            ->visible(function ($record) {
                                return $record->suiviEnFcm &&
                                    $record->complements_fcm &&
                                    !$record->complements_fcm->parcoursSerialises()->exists();
                            })
                    ])
                    ->action(function ($record, $data) {
                        if (array_key_exists("cohorte_id", $data) && $data["cohorte_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                ["cohorte" => $data["cohorte_id"]]
                            );
                            event($event);
                        }

                        if (array_key_exists("mentor_id", $data) && $data["mentor_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                ["mentor" => $data["mentor_id"]]
                            );
                            event($event);
                        }

                        if (array_key_exists("parcours_id", $data) && $data["parcours_id"] != null){
                            $event = new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                ["parcours" =>  $data["parcours_id"]]
                            );
                            event($event);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::canAccessAdminMenus()), // Seuls les admins
                ]),
            ]);
    }

    /**
     * Compte les activités en attente de validation pour un marin
     */
    public static function getActivitesEnAttenteCount($marin): int
    {
        try {
            if (!$marin->suiviEnFcm || !$marin->complements_fcm) {
                return 0;
            }

            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
            if (!$parcours) {
                return 0;
            }

            $pendingCount = 0;
            foreach ($parcours['fonctions'] ?? [] as $fonction) {
                foreach ($fonction['competences'] ?? [] as $competence) {
                    foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                        foreach ($savoirfaire['activites'] ?? [] as $activite) {
                            $estProposee = !empty($activite['date_proposition']);
                            $estValidee = ($activite['etat_valid'] ?? false) === true;
                            $aDateValidation = !empty($activite['date_validation']);
                            
                            if ($estProposee && !$estValidee && !$aDateValidation) {
                                $pendingCount++;
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Bouton principal pour ajouter un marin
        if (static::canAccessAdminMenus()) {
            // $actions[] = Actions\Action::make('ajouter-marin')
            //     ->label('Ajouter un marin')
            //     ->icon('heroicon-o-user-plus')
            //     ->color('success')
            //     ->button()
            //     ->url(fn () => RechercheAnnuairePage::getUrl())
            //     ->tooltip('Rechercher dans l\'annuaire pour ajouter un marin');
        }

        // Bouton pour les mentors (accès à leurs infos)
        if (static::canAccessMentorFeatures()) {
            // $actions[] = Actions\Action::make('mes-statistiques')
            //     ->label('Mes statistiques')
            //     ->icon('heroicon-o-chart-bar')
            //     ->color('info')
            //     ->button()
            //     ->action(function () {
            //         // Afficher modal avec statistiques du mentor
            //         $this->dispatch('open-modal', id: 'mentor-stats');
            //     })
            //     ->tooltip('Voir mes statistiques de mentor');
        }

        return $actions;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarins::route('/'),
            'livret-de-fcm' => Pages\LivretDeFcmLight::route('/{record}/livret-de-fcm-light'),
        ];
    }


    

    // Optionnel : Titre personnalisé selon les droits
    public function getTitle(): string
    {
        if (static::canAccessMentorFeatures() && !static::canAccessAdminMenus()) {
            return 'Mes marins supervisés';
        }
        
        return 'Gestion des marins';
    }

    // Optionnel : Sous-titre avec informations contextuelles
    public function getSubheading(): ?string
    {
        try {
            if (static::canAccessMentorFeatures() && !static::canAccessAdminMenus()) {
                $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
                if ($currentMarin) {
                    $count = \Modules\FcmCentral\Models\FcmMarin::where('mentor_id', $currentMarin->id)->count();
                    $pending = MarinResource::getActivitesEnAttenteCount($currentMarin);
                    
                    return "Vous supervisez {$count} marins. {$pending} activités en attente de validation.";
                }
            }
            
            if (static::canAccessAdminMenus()) {
                $totalMarins = \Modules\FcmCentral\Models\Marin::enFcm()->count();
                return "Gestion de {$totalMarins} marins en formation continue.";
            }
        } catch (\Exception $e) {
            // En cas d'erreur, pas de sous-titre
        }
        
        return null;
    }



    
}
