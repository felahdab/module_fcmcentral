<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Illuminate\Database\Eloquent\Builder;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;

use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;

use Modules\RH\Models\Specialite;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\FcmMarin;

class MarinResource extends Resource
{
    protected static ?string $model = Marin::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = "Marins";

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Badge de navigation avec comptage selon les droits
     */
    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->can("fcmcentral::voir_tous_les_marins")){
            return (string) Marin::enFcm()->count();
        }

        $currentMarin = Marin::fromCurrentUser();

        if ($currentMarin) {
            return FcmMarin::where('mentor_id', $currentMarin->id)->count();
        }
       
        return "0";
    }

    /**
     * Filtrage automatique selon les droits
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /**
         * Si l'utilisateur n'est pas authentifié, on renvoie une requête qui bloque tout accès.
         */
        if (!auth()->check())
        {
            return $query->whereRaw('1=0');
        }

        /**
         * A partir de ce point, on sait que l'utilisateur est authentifié.
         */

        
        // Les admins voient tout
        if (auth()->user()->can("fcmcentral::voir_tous_les_marins")){
            return $query;
        }

        $currentMarin = Marin::fromCurrentUser();

        if ($currentMarin && $currentMarin->est_mentor) {
            $marinIds = FcmMarin::where('mentor_id', $currentMarin->id)
                ->pluck('rh_marin_id');
            
            return $query->whereIn('id', $marinIds);
        }
    
        // Par défaut, query vide
        return $query->whereRaw('1 = 0');
    }


    

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nom')->required()->maxLength(1500),
            Forms\Components\TextInput::make('prenom')->required()->maxLength(1000),
            Forms\Components\TextInput::make('email')->email()->required()->unique(table: 'rh_marins', column: 'email', ignoreRecord: true),
            Forms\Components\TextInput::make('nid')->maxLength(15)->required(),
            Forms\Components\TextInput::make('matricule')->maxLength(20),
            Forms\Components\Select::make('grade_id')->relationship('grade', 'libelle_long'),
            Forms\Components\Select::make('specialite_id')->relationship('specialite', 'libelle_court'),
            Forms\Components\Select::make('brevet_id')->relationship('brevet', 'libelle_long'),
            Forms\Components\Select::make('unite_id')->relationship('unite', 'libelle_long'),
        ]);
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->defaultSort('nom', 'asc')
           
            ->headerActions([
                   /** Telechargement CSV */
                   Action::make('downloadCsv')
                   ->label('Exporter les marins')
                   ->icon('heroicon-o-arrow-down-tray')
                   ->color('primary')
                   ->action(function () {
                       $marins = \Modules\FcmCentral\Models\Marin::with([
                           'grade', 
                           'specialite', 
                           'unite', 
                           'complementsFcm.cohorte',
                           'etats.typeEtat'
                       ])->get();
           
                       $csvHeader = [
                           'GRADE', 'NOM', 'PRENOM', 'SPECIALITE', 'AFFECTATION', 'COHORTE',
                           'GEL FCM', 'VERT PPA DEPUIS LE', 'TEMPS DANS LE PPA', 
                           'PROGRESSION GLOBALE', 'STAGE MANQUANTS'
                       ];
                       
                       $callback = function() use ($csvHeader, $marins) {
                           $file = fopen('php://output', 'w');
                           fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
                           fputcsv($file, $csvHeader, ';');
                           
                           foreach ($marins as $marin) {
                               // GEL FCM
                               $gelFcm = $marin->etats()
                                   ->whereHas('typeEtat', fn($q) => $q->where('code', 'FCM_ENP'))
                                   ->orderBy('date_debut')
                                   ->first()?->date_debut;
                               
                               // VERT PPA
                               $vertPpa = $marin->etats()
                                   ->whereHas('typeEtat', fn($q) => $q->where('code', 'FCM_ACT'))
                                   ->orderBy('date_debut')
                                   ->first()?->date_debut;
                               
                               // TEMPS PPA
                               $dateDebut = $marin->etats()->orderBy('date_debut')->first()?->date_debut;
                               $tempsPpa = $dateDebut ? \Carbon\Carbon::parse($dateDebut)->diffInDays(now()) : '0';
                               $tempsPpa =  round($tempsPpa,0);
                               
                               // PROGRESSION
                               $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
                               $progression = $parcours ? (new \Modules\FcmCommun\Services\CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcours) : null;
                               
                               // STAGES MANQUANTS
                               $stagesManquants = 0;
                               if ($parcours) {
                                   foreach ($parcours['affectations'] ?? [] as $affectation) {
                                       foreach ($affectation['competences'] ?? [] as $competence) {
                                           foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                                               if (($csf['savoirfaire_type']['code'] ?? null) === 'S' && empty($csf['etat_valid'])) {
                                                   $stagesManquants++;
                                               }
                                           }
                                       }
                                   }
                               }
                               
                               fputcsv($file, [
                                   $marin->grade?->libelle_court ?? '',
                                   $marin->nom ?? '',
                                   $marin->prenom ?? '',
                                   $marin->specialite?->libelle_long ?? '',
                                   $marin->unite?->libelle_long ?? '',
                                   $marin->complementsFcm?->cohorte?->libelle_long ?? '',
                                   $gelFcm ? \Carbon\Carbon::parse($gelFcm)->format('d/m/Y') : 'Non renseigné',
                                   $vertPpa ? \Carbon\Carbon::parse($vertPpa)->format('d/m/Y') : 'Non renseigné',
                                   $tempsPpa ?? '',
                                   $progression ?? '',
                                   $stagesManquants,
                               ], ';');
                           }
                           fclose($file);
                       };
           
                       return \Illuminate\Support\Facades\Response::stream($callback, 200, [
                           "Content-Type" => "text/csv; charset=UTF-8",
                           "Content-Disposition" => "attachment; filename=marins-fcm.csv",
                       ]);
                   })
                   ->visible(fn() => auth()->check() && auth()->user()->can("fcmcentral::exporter_csv")),
           
                   /** Fin Telechargment */
                
                Tables\Actions\Action::make('recherche-annuaire')
                    ->label('Ajouter un marin')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->button()
                    ->url(fn() => RechercheAnnuairePage::getUrl())
                    ->visible(fn() => RechercheAnnuairePage::canAccess())
            ])
            ->columns([
                Tables\Columns\TextColumn::make('grade.libelle_court')->searchable(),
                Tables\Columns\TextColumn::make('unite.libelle_court')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('unite.lieuUnite.libelle_court')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prenom')->searchable()->label('Prénom'),
                
                // 🎯 FCM ACTIF (Oui/Non)
                // Colonne FCM avec gestion du typeEtat
                Tables\Columns\IconColumn::make('fcm_actif')
                    ->label('FCM')
                    ->boolean()
                    ->state(fn ($record) => $record->suivi_en_fcm && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'heroicon-c-shield-check' : 'heroicon-o-x-circle')
                    ->trueColor(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'gray' : 'success')
                    ->falseColor(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'gray' : 'danger')
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isFilterEnFcmActive') && $livewire->isFilterEnFcmActive())
                    ->action(
                        Tables\Actions\Action::make('toggle_fcm')
                            ->modalHeading(fn ($record) => $record->suivi_en_fcm ? 'Marin en FCM' : 'Activer FCM')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->requiresConfirmation()
                            ->form(function ($record) {
                                if ($record->suivi_en_fcm && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN') {
                                }
                                
                                return [
                                    Forms\Components\Select::make('cohorte_id')
                                        ->label("Choisir la cohorte")
                                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.cohorte'))
                                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                                    Forms\Components\Select::make('mentor_id')
                                        ->label("Choisir un mentor")
                                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.mentor'))
                                        ->options(
                                            Marin::query()
                                                ->whereNull('deleted_at')
                                                ->orderBy('nom')
                                                ->orderBy('prenom')
                                                ->get()
                                                ->mapWithKeys(function ($marin) {
                                                    $fullName = trim($marin->prenom . ' ' . $marin->nom);
                                                    $grade = $marin->grade?->libelle_court ?? '';
                                                    $label = $grade ? $grade . ' ' . $fullName : $fullName;
                                                    return [$marin->uuid => $label];
                                                })
                                        )
                                        ->searchable(),
                                    Forms\Components\Select::make('parcours_id')
                                        ->label('Choisir un Parcours')
                                        ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                                        ->searchable()
                                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.parcours'))
                                ];
                            })
                            ->action(function ($record, $data) {
                                
                                if ($record->estEnFcm() && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Marin déjà en FCM')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                                
                                // Activation FCM + historisation état FCM_sed
                                event(new UserGeneratedEvent(
                                    UserGeneratedEventTypes::MARIN_EN_FCM,
                                    auth()->user()->uuid,
                                    ObjectClasses::MARIN,
                                    $record->uuid,
                                    ['statut_fcm' => 'En validation']
                                ));
                                // Attribution cohorte/mentor/parcours
                                if (!empty($data['cohorte_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["cohorte" => $data["cohorte_id"]]
                                    ));
                                }
                                if (!empty($data['mentor_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["mentor" => $data["mentor_id"]]
                                    ));
                                }
                                if (!empty($data['parcours_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["parcours" => $data["parcours_id"]]
                                    ));
                                }
                            })
                            ->visible(fn($record) =>
                                $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN' &&
                                auth()->user()->canAny(["fcmcentral::attribuer.cohorte", "fcmcentral::attribuer.mentor", "fcmcentral::attribuer.parcours"])
                            )
                    ),

                // Colonne Type Etat FCM dynamique
                Tables\Columns\TextColumn::make('type_jalon_fcm')
                    ->label('Jalon')
                    ->state(function ($record) {
                        if (!$record->suivi_en_fcm) return null;
                        return $record->complementsFcm?->getJalonFcmLibelle();
                    })
                    ->badge()
                    ->color(function ($record) {
                        $etat = $record->complementsFcm?->getJalonFcmLibelle();
                        return match($etat) {
                            'SED' => 'info',
                            'FCM' => 'success',
                            'FIN' => 'gray',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($record) {
                        $etat = $record->complementsFcm?->getJalonFcmLibelle();
                        return match($etat) {
                            'SED' => 'heroicon-s-arrow-path',
                            'FCM' => 'heroicon-s-check-circle',
                            'FIN' => 'heroicon-o-minus-circle',
                            default => 'heroicon-o-minus-circle',
                        };
                    })
                    ->placeholder('Non assigné')
                    ->tooltip('Cliquer pour modifier')
                    ->action(
                        Tables\Actions\Action::make('jalons_fcm')
                            ->modalHeading('Modifier les Jalons FCM')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->form([
                                Forms\Components\DatePicker::make('date_sedimentation')
                                    ->label('Date de début d\'entrée en FCM')
                                    ->helperText('Date de début d\'entrée en FCM')
                                    ->default(fn ($record) => $record->complementsFcm?->date_sedimentation),
                                Forms\Components\DatePicker::make('date_fcm')
                                    ->label('Date de fin de sédimentation ')
                                    ->helperText('Date de fin de sédimentation (laisser vide pour changer de statut)')
                                    ->default(fn ($record) => $record->complementsFcm?->date_fcm),
                                Forms\Components\DatePicker::make('date_competence')
                                    ->label('Date de vert compétence')
                                    ->helperText('Date de vert compétence (laisser vide pour changer de statut)')
                                    ->default(fn ($record) => $record->complementsFcm?->date_competence),
                            ])
                            ->action(function ($record, array $data) {
                                if (!empty($data['date_sedimentation'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_sedimentation" => $data["date_sedimentation"]]
                                    ));
                                }
                                if (!empty($data['date_fcm'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_fcm" => $data["date_fcm"]]
                                    ));
                                }
                                if (!empty($data['date_competence'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_competence" => $data["date_competence"]]
                                    ));
                                }
                        })
                        ->visible(fn ($record) =>
                            $record->suivi_en_fcm
                            && auth()->check()
                            && auth()->user()->can("fcmcentral::definir_jalons_de_fcm")
                        )
                ),


                Tables\Columns\TextColumn::make('complementsFcm.cohorte.libelle_court')
                    ->label('Cohorte')
                    ->searchable()
                    //->sortable()
                    ->toggleable()
                    ->placeholder('Non assignée')
                    // ->action(
                    //     Tables\Actions\Action::make('gerer_cohorte')
                    //         ->modalHeading('Attribuer une cohorte')
                    //         ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                    //         ->form([
                    //             Forms\Components\Select::make('cohorte_id')
                    //                 ->label('Choisir une cohorte')
                    //                 ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid'))
                    //                 ->searchable(),
                    //         ])
                    //         ->action(function ($record, array $data) {
                    //             if (!empty($data['cohorte_id'])) {
                    //                 event(new UserGeneratedEvent(
                    //                     UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                    //                     auth()->user()->uuid,
                    //                     ObjectClasses::MARIN,
                    //                     $record->uuid,
                    //                     ["cohorte" => $data["cohorte_id"]]
                    //                 ));
                    //             }
                    //         })
                    //         ->visible(fn ($record) => $record->estEnFcm() && 
                    //             auth()->check() &&
                    //             auth()->user()->can("fcmcentral::attribuer.cohorte")
                    //         )
                    //     )
                    ,

                // 👤 MENTOR CLIQUABLE
                Tables\Columns\TextColumn::make('complementsFcm.mentor.nom')
                    ->label('Mentor')
                    ->formatStateUsing(fn ($record) => $record->getMentor() ?
                        ($record->getMentor()->grade?->libelle_court ?? '') . ' ' . $record->getMentor()->nom :
                        'Non assigné'
                    )
                    ->searchable()
                    ->placeholder('Non assigné')
                    ->badge()
                    ->color(fn ($record) => $record->getMentor() ? 'success' : 'gray')
                    ->icon(fn ($record) => $record->getMentor() ? 'heroicon-s-user-circle' : 'heroicon-o-user-circle')
                    ->action(
                        Tables\Actions\Action::make('gerer_mentor')
                            ->modalHeading('Gestion du mentor')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->form([
                                Forms\Components\Select::make('mentor_id')
                                    ->label('Choisir un mentor')
                                    ->options(
                                        Marin::query()
                                            ->whereNull('deleted_at')
                                            ->orderBy('nom')
                                            ->get()
                                            ->mapWithKeys(fn ($m) => [
                                                $m->uuid => ($m->grade?->libelle_court ?? '') . ' ' . $m->prenom . ' ' . $m->nom
                                            ])
                                    )
                                    ->searchable(),
                                // Forms\Components\Toggle::make('retirer_mentor')
                                //     ->label('Retirer le mentor')
                                //     ->visible(fn ($record) => $record->getMentor() !== null),
                            ])
                            ->action(function ($record, array $data) {
                                // if (!empty($data['retirer_mentor'])) {
                                //     $record->complementsFcm->update(['mentor_id' => null]);
                                // } elseif (!empty($data['mentor_id'])) {
                                    if(!empty($data['mentor_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["mentor" => $data["mentor_id"]]
                                    ));
                                }
                            })
                            ->visible(fn ($record) => $record->estEnFcm() && 
                                    auth()->check() &&
                                    auth()->user()->can("fcmcentral::attribuer.mentor")
                            )),
                    
                // 📚 PARCOURS CLIQUABLE
                Tables\Columns\TextColumn::make('complementsFcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label('Parcours')
                    ->formatStateUsing(fn ($record) =>
                        $record->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné'
                    )
                    ->badge()
                    ->color(fn ($record) => $record->complementsFcm?->donnees_du_parcours ? 'success' : 'gray')
                    ->icon(fn ($record) => $record->complementsFcm?->donnees_du_parcours ? 'heroicon-s-book-open' : 'heroicon-s-plus')
                    ->placeholder('Non assigné')
                    ->action(
                        Tables\Actions\Action::make('gerer_parcours')
                            ->modalHeading('Gestion du parcours')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->form([
                                Forms\Components\Select::make('parcours_id')
                                    ->label('Choisir un parcours')
                                    ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                                    ->searchable(),
                            ])
                            ->action(function ($record, array $data) {
                                if (!empty($data['parcours_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["parcours" => $data["parcours_id"]]
                                    ));
                                }
                            })
                            ->visible(fn ($record) => $record->estEnFcm() && 
                                auth()->check() &&
                                auth()->user()->can("fcmcentral::attribuer.parcours")
                            )),

                Tables\Columns\TextColumn::make('complementsFcm.donnees_du_parcours.taux_global')
                    ->label('Taux')
                    //->sortable()
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => intval(round($state)))
                    ->placeholder('0'),

                // 🔔 ACTIVITÉS EN ATTENTE
                Tables\Columns\TextColumn::make('activites_en_attente')
                    ->label('En attente')
                    ->state(fn ($record) => static::getActivitesEnAttenteCount($record))
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                    ->icon(fn (int $state): string => $state > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                    ->extraAttributes(['class'=>'cursor-default'])
                    //->tooltip(fn (int $state): string => $state > 0 ? "$state activité(s) en attente de validation" : 'Aucune activité en attente'),
            ])
            
            ->filters([
                Filter::make('en_fcm')
                ->form([
                    Select::make('value')
                        ->label('Statut FCM')
                        ->options([
                           
                            'enFcm' => 'Marins en FCM uniquement',
                            'horsFcm' => 'Marins hors FCM',
                        ])
                       ->placeholder('Tous les Marins')
                       ->default('enFcm')
                       ->native(false),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['value'])){
                        return $query;
                    }

                    return match($data['value'] ?? 'enFcm') {
                        'enFcm' => $query->enFcm(),
                        'horsFcm' => $query->pasEnFcm(),
                        default => $query,
                    };
                })
                ->indicateUsing(function (array $data): ?string {
                    if (empty($data['value'])){
                        return null;
                    }

                    return match($data['value'] ?? 'enFcm') {
                        'enFcm' => '✓ Marins en FCM uniquement',
                        'horsFcm' => '✓ Marins hors FCM',
                        default => null,
                    };
                }),
        
                SelectFilter::make('specialite_id')
                    ->label('Spécialité')
                    ->options(Specialite::query()->orderBy('libelle_court')->pluck('libelle_court', 'id')),

                   
                    
                    
            ])

            
            // ICONES DROITE

            ->actions([

                //EDIT
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn ($record) => static::canEdit($record)),
                // VOIR LIVRET    
                Tables\Actions\Action::make('livret-de-fcm')
                    ->iconButton()
                    ->icon('heroicon-m-book-open')
                    ->color('info')
                    ->tooltip('Livret FCM')
                    ->extraAttributes([
                        'title' => 'Livret FCM',
                        'class' => 'btn-livret',
                    ])
                    ->visible(fn ($record) => $record->complementsFcm?->donnees_du_parcours &&
                        auth()->check() &&
                        auth()->user()->can("fcmcentral::livret.voir"))
                    ->url(fn ($record) => static::getUrl('livret-de-fcm', ['record' => $record->id])),

                // FICHE BILAN
                Tables\Actions\Action::make('fiche-bilan')
                    ->iconButton()
                    ->icon('heroicon-m-chart-bar-square')
                    ->color('warning')
                    ->extraAttributes([
                        'title' => 'Fiche Bilan FCM',
                        'class' => 'btn-bilan',
                    ])
                    ->visible(fn ($record) => MarinResource\Pages\FicheBilan::canAccess(["record" => $record]))
                    ->url(fn($record) => MarinResource\Pages\FicheBilan::getUrl(['record' => $record->id])),
                // ICONE PDF
                Tables\Actions\Action::make('livret-pdf')
                    ->iconButton()
                    ->icon('heroicon-m-document-text')
                    ->color('secondary')
                    ->extraAttributes([
                        'title' => 'Télécharger PDF du Livret FCM',
                        'class' => 'btn-pdf',
                    ])
                    ->visible(fn ($record) => $record->complementsFcm?->donnees_du_parcours &&
                        auth()->check() &&
                        auth()->user()->can("fcmcentral::livret.telecharger"))
                    ->action(function ($record) {
                        return response()->streamDownload(function () use ($record) {
                            echo app(\Modules\FcmCommun\Services\LivretPdfService::class)->generatePdf($record);
                        }, "livret-fcm-{$record->nom}-{$record->prenom}.pdf", [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),

                    // ✅ NOUVEAU BOUTON NOTE
                Tables\Actions\Action::make('create-note')
                ->iconButton()
                ->icon('heroicon-m-newspaper')
                ->color('danger')
                ->extraAttributes([
                    'title' => 'Créer une note',
                    'class' => 'btn-note',
                ])
                ->visible(fn ($record) => $record->suivi_en_fcm &&
                        auth()->check() &&
                        auth()->user()->can("fcmcentral::note.creer"))
                ->modalHeading('Créer une note')
                ->modalDescription(fn ($record) => "Créer une note pour {$record->prenom} {$record->nom}")
                ->modalWidth('lg')
                ->form([
                    Forms\Components\Textarea::make('libelle')
                        ->required()
                        ->label('Contenu de la note')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('Saisissez le contenu de votre note...'),
                
                    Forms\Components\Hidden::make('marin_id')
                        ->default(fn ($record) => $record->id),
                
                    Forms\Components\Placeholder::make('marin_selected')
                        ->label('Marin concerné')
                        ->content(fn ($record) => $record->grade?->libelle_court . ' ' . $record->prenom . ' ' . $record->nom),
                
                    /*
                    Forms\Components\Select::make('type_note_id')
                        ->label('Type de note')
                        ->options(function() {
                            return \Modules\FcmCentral\Models\TypeNote::pluck('libelle_court', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\Textarea::make('code')
                                ->required()
                                ->label('code'),
                            Forms\Components\TextInput::make('libelle_court')
                                ->required()
                                ->label('Libellé court')
                                ->maxLength(150),
                        ])
                        ->createOptionUsing(function (array $data) {
                            return \Modules\FcmCentral\Models\TypeNote::create($data)->id;
                        }),
                    */
                
                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->default(now())
                        ->nullable(),
                
                    /*
                    Forms\Components\Select::make('redacteur_id')
                        ->label('Rédacteur')
                        ->options(function () {
                            return \Modules\FcmCentral\Models\Marin::orderBy('nom')
                                ->get()
                                ->mapWithKeys(fn($marin) => [
                                    $marin->id => ($marin->grade?->libelle_court . ' ' . $marin->prenom . ' ' . $marin->nom)
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->default(function () {
                            $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
                            return $currentMarin ? $currentMarin->id : auth()->user()->id;
                        }),
                    */
                
                    /*
                    Forms\Components\Toggle::make('valide_bcf')
                        ->label('Validé BCF')
                        ->default(false)
                        ->visible(fn () => auth()->user()->can("fcmcentral::note.valider")),
                    */
                
                    Forms\Components\TextInput::make('lien_url')
                        ->label('Lien URL (optionnel)')
                        ->url()
                        ->nullable()
                        ->placeholder('https://...'),
                
                    Forms\Components\Hidden::make('objet_type')
                        ->default('marin'),
                
                    Forms\Components\Hidden::make('objet_id')
                        ->default(fn ($record) => $record->id),
                ])
                ->action(function ($record, array $data) {
                    try {
                        \Log::info('Tentative de création de note', [
                            'record_id' => $record->id,
                            'data' => $data,
                        ]);
                
                        // Détermination du rédacteur (user connecté)
                        $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
                        $redacteurId =  auth()->user()->id;
                
                        // Créer la note
                        $note = \Modules\FcmCentral\Models\Note::create([
                            'libelle' => $data['libelle'],
                            'rh_marin_id' => $record->id,
                            //'marin_id' => $record->complementsFcm->id,
                            // 'type_note_id' => $data['type_note_id'] ?? null, // Champ commenté
                            'date' => $data['date'] ?? now(),
                            'redacteur_id' => $redacteurId, // Utilisateur connecté
                            'valide_bcf' => false, // Champ commenté mais valeur prise en compte
                            'lien_url' => $data['lien_url'] ?? null,
                            'objet_type' => 'marin',
                            'objet_id' => $record->id,
                        ]);
                
                        \Filament\Notifications\Notification::make()
                            ->title('Note créée avec succès')
                            ->body("La note pour {$record->prenom} {$record->nom} a été créée.")
                            ->success()
                            ->send();
                
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors de la création de la note', [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                            'record_id' => $record->id,
                            'data' => $data,
                        ]);
                
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur lors de la création')
                            ->body('Une erreur est survenue lors de la création de la note.')
                            ->danger()
                            ->send();
                    }
                })
                
                ->requiresConfirmation()
                ->modalSubmitActionLabel('Créer la note')
                ->modalCancelActionLabel('Annuler'),
                // Fin Note
            ]);
    }


   

    /**
     * Compte les activités en attente de validation (objectifs + savoir-faires)
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
                        
                            $pendingCount++;
                        }

                        // Vérifier les objectifs
                        foreach ($savoirfaire['objectifs'] ?? [] as $objectif) {
                            $objPropose = !empty($objectif['date_proposition']);
                            $objValide = ($objectif['etat_valid'] ?? false) === true;
                            
                            if ($objPropose && !$objValide) {
                                //if ($objPropose ) {
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarins::route('/'),
            'livret-de-fcm' => Pages\LivretDeFcmLight::route('/{record}/livret-de-fcm-light'),
            'fiche-bilan' => Pages\FicheBilan::route('/{record}/fiche-bilan'),
        ];
    }
}
