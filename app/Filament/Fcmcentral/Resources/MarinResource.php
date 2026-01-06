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
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\RH\Models\Specialite;
use Modules\FcmCentral\Models\Role;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;
use Modules\FcmCentral\Traits\FcmPermissionTrait;


//use Modules\FcmCentral\Filament\Fcmcentral\Exports\MarinBcfExporter;
//use Filament\Tables\Actions\ExportAction;
//use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Tables\Actions\Action;

class MarinResource extends Resource
{
    use FilamentPermissionTrait, FcmPermissionTrait;
    
    protected static ?string $model = Marin::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = "Marins";

    protected static array $permissions = [
        'fcmcentral::marin.index',
        'fcmcentral::admin',
        'fcmcentral::permission.bcf',
        'fcmcentral::permission.fps',
    ];

    // Retirer bouton Creer Create
    public static function canCreate(): bool
    {
        return false;
    }

    // Creer fonction pour RH Creation MArin pour ne pas envoyer fcmcentral::
    public static function canAccessAdminMenus() : bool
    {
        return static:: canAccessAnyPermission([
            'permission.mentor',
            'permission.bcf',
            'permission.fps'
        ]);
    }

    


     

    /**
     * Badge de navigation avec comptage selon les droits
     */
    public static function getNavigationBadge(): ?string
    {
        try {
            if (static:: canAccessPermission('admin')) {
                // Admins voient tous les marins FCM
                return (string) Marin::enFcm()->count();
            }
            
            if (static:: canAccessAnyPermission([
                'permission.mentor',
                'permission.bcf',
                'permission.fps'
            ])) {
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
 * Vérifie si l'utilisateur connecté est le mentor du marin donné
 */
public static function isMentorOfMarin(Marin $marin): bool
{
    try {
        $currentMarin = Marin::fromCurrentUser();
       
        if (!$currentMarin) {
            \Log::warning('isMentorOfMarin: utilisateur sans compte marin', [
                'user_id' => auth()->id(),
            ]);
            return false;
        }

        // Vérifier si le currentMarin est mentor du marin passé en paramètre
        $isMentor = FcmMarin::where('mentor_id', $currentMarin->id)
            ->where('rh_marin_id', $marin->id)
            ->exists();
       
        \Log::info('Vérification mentor', [
            'current_marin_id' => $currentMarin->id,
            'target_marin_id' => $marin->id,
            'is_mentor' => $isMentor,
        ]);
       
        return $isMentor;
       
    } catch (\Exception $e) {
        \Log::error('Erreur lors de la vérification du mentor', [
            'message' => $e->getMessage(),
            'marin_id' => $marin->id ?? null,
            'user_id' => auth()->id(),
        ]);
        return false;
    }
}




    /**
     * Filtrage automatique selon les droits
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        try {
            // Les admins voient tout
            if (static:: canAccessAnyPermission([
                'admin',
                'permission.bcf',
                'permission.fps'])) {
                return $query;
            }
            
            // Les mentors voient seulement leurs marins
            if (static:: canAccessAnyPermission([
                'permission.mentor',
                ]
                )) {
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

                    // Cette methode ne fait que telecahrger sur le serveur (app/storage) , on veut telecahrger en direct
                    // ExportAction::make('export')
                    // ->label('Exporter les marins')
                    // ->exporter(MarinBcfExporter::class)
                    // ->color('primary')
                    // ->formats([ExportFormat::Csv])
                    // ->icon('heroicon-o-arrow-down-tray')
                    // ->fileName('marins-fcm.csv')
                    // ->visible(fn() => static::canAccessAnyPermission(['admin',  'permission.bcf']))
                    // ,
               
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
                   ->visible(fn() => static::canAccessAnyPermission(['admin', 'permission.bcf'])),
           
                   /** Fin Telechargment */
                
                Tables\Actions\Action::make('recherche-annuaire')
                    ->label('Ajouter un marin')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->button()
                    ->url(fn() => RechercheAnnuairePage::getUrl())
                    ->visible(fn() => static::canAccessAnyPermission(['admin', 'permission.mentor', 'permission.bcf', 'permission.fps']))
            ])
            ->columns([
                Tables\Columns\TextColumn::make('grade.libelle_court')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prenom')->searchable()->label('Prénom'),
                
                // 🎯 FCM ACTIF (Oui/Non)
                // Colonne FCM avec gestion du typeEtat
Tables\Columns\IconColumn::make('fcm_actif')
->label('FCM')
->boolean()
->state(fn ($record) => $record->estEnFcm() && $record->getDernierTypeEtatCode() !== 'FCM_FIN')
->trueIcon('heroicon-s-check-circle')
->falseIcon(fn ($record) => $record->getDernierTypeEtatCode() === 'FCM_FIN' ? 'heroicon-c-shield-check' : 'heroicon-o-x-circle')
->trueColor(fn ($record) => $record->getDernierTypeEtatCode() === 'FCM_FIN' ? 'gray' : 'success')
->falseColor(fn ($record) => $record->getDernierTypeEtatCode() === 'FCM_FIN' ? 'gray' : 'danger')
->action(
    Tables\Actions\Action::make('toggle_fcm')
        ->modalHeading(fn ($record) => $record->estEnFcm() ? 'Marin en FCM' : 'Activer FCM')
        ->modalDescription(fn ($record) => $record->fullNameAndGrade)
        ->requiresConfirmation()
        ->form(function ($record) {
            if ($record->estEnFcm() && $record->getDernierTypeEtatCode() !== 'FCM_FIN') {
               // return []; // Pas de formulaire si déjà actif et pas en FCM_FIN
               $event = new UserGeneratedEvent(
                UserGeneratedEventTypes::MARIN_PAS_FCM,
                auth()->user()->uuid,
                ObjectClasses::MARIN,
                $record->uuid,
                []
            );
            event($event);
            }
            return [
                Forms\Components\Select::make('cohorte_id')
                    ->label("Choisir la cohorte")
                    ->visible(fn() => static::canAccessPermission('attribuer.cohorte'))
                    ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                Forms\Components\Select::make('mentor_id')
                    ->label("Choisir un mentor")
                    ->visible(fn() => static::canAccessPermission('attribuer.mentor'))
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
                    ->visible(fn() => static::canAccessPermission('attribuer.parcours')),
            ];
        })
        ->action(function ($record, $data) {
            
            if ($record->estEnFcm() && $record->getDernierTypeEtatCode() !== 'FCM_FIN') {
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
            static::canAccessAnyPermission(['admin','permission.mentor', 'permission.bcf', 'permission.fps'])
            && ($record->getDernierTypeEtatCode() !== 'FCM_FIN')
        )
),

// Colonne Type Etat FCM dynamique
Tables\Columns\TextColumn::make('type_etat_fcm')
->label('Type état')
->state(function ($record) {
    if (!$record->estEnFcm()) return null;
    return $record->getDernierTypeEtatLibelle();
})
->badge()
->color(fn ($record) => match($record->getDernierTypeEtatCode()) {
    'FCM_ACT' => 'success',
    'FCM_SED' => 'info',
    'FCM_PAU' => 'warning',
    'FCM_FIN' => 'gray',
    default => 'gray',
})
->icon(fn ($record) => match($record->getDernierTypeEtatCode()) {
    'FCM_ACT' => 'heroicon-s-check-circle',
    'FCM_SED' => 'heroicon-s-arrow-path',
    'FCM_PAU' => 'heroicon-s-pause-circle',
    'FCM_FIN' => 'heroicon-o-minus-circle',
    default => 'heroicon-o-minus-circle',
})
->placeholder('Non assigné')
->tooltip('Cliquer pour modifier')
->action(
    Tables\Actions\Action::make('modifier_type_etat')
        ->modalHeading('Modifier le type d\'état FCM')
        ->modalDescription(fn ($record) => $record->fullNameAndGrade)
        ->form([
            Forms\Components\Select::make('type_etat_id')
                ->label('Type d\'état')
                ->options(
                    \Modules\FcmCentral\Models\TypeEtat::query()
                        ->orderBy('libelle_court')
                        ->pluck('libelle_court', 'id')
                )
                ->required()
                ->default(fn ($record) => $record->getDernierTypeEtatId()),

                Forms\Components\DatePicker::make('date_debut')
                ->label('Date de début')
                ->helperText('La date ci dessus sera la date de fin de l\'etat en cours')
                ->required()
                ->default(now()),
        ])
        ->action(function ($record, array $data) {
            // Historiser chaque changement dans la table etat
            // $fcmMarin = $record->complementsFcm;
            // if ($fcmMarin) {
                // Mettre date de fin au statut actuel
               // $etatActuel = $fcmMarin->etats()->latest('date_debut')->first();
               $etatActuel = $record->etats()->latest('date_debut')->first();
                if ($etatActuel && !$etatActuel->date_fin){
                    //$etatActuel->update(['date_fin'=>now()]);
                    $dateFin = \Carbon\Carbon::parse($data['date_debut']);
                    $etatActuel->update(['date_fin'=>$dateFin]);
                }
                $typeEtat = \Modules\FcmCentral\Models\TypeEtat::find($data['type_etat_id']);
               // $record->historiserEtat($typeEtat->code);
               $dateDebut = \Carbon\Carbon::parse($data['date_debut']);
               $record->historiserEtat($typeEtat->code, $dateDebut);
            //}
        })
        ->visible(fn ($record) =>
            $record->estEnFcm()
            //&& $record->getDernierTypeEtatCode() !== 'FCM_FIN'
            && static::canAccessAnyPermission(['admin', 'permission.bcf', 'permission.fps'])
        )
),


                Tables\Columns\TextColumn::make('complements_fcm.cohorte.libelle_court')
                    ->label('Cohorte')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Non assignée')
                    ->action(
                        Tables\Actions\Action::make('gerer_cohorte')
                            ->modalHeading('Attribuer une cohorte')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->form([
                                Forms\Components\Select::make('cohorte_id')
                                    ->label('Choisir une cohorte')
                                    ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid'))
                                    ->searchable(),
                            ])
                            ->action(function ($record, array $data) {
                                if (!empty($data['cohorte_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["cohorte" => $data["cohorte_id"]]
                                    ));
                                }
                            })
                            ->visible(fn ($record) => $record->estEnFcm() && static::canAccessPermission('attribuer.cohorte'))
                    ),

                // 👤 MENTOR CLIQUABLE
                Tables\Columns\TextColumn::make('complements_fcm.mentor.nom')
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
                                Forms\Components\Toggle::make('retirer_mentor')
                                    ->label('Retirer le mentor')
                                    ->visible(fn ($record) => $record->getMentor() !== null),
                            ])
                            ->action(function ($record, array $data) {
                                if (!empty($data['retirer_mentor'])) {
                                    $record->complements_fcm->update(['mentor_id' => null]);
                                } elseif (!empty($data['mentor_id'])) {
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["mentor" => $data["mentor_id"]]
                                    ));
                                }
                            })
                            ->visible(fn ($record) => $record->estEnFcm() && static::canAccessPermission('attribuer.mentor'))
                    ),

                // 📚 PARCOURS CLIQUABLE
                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label('Parcours')
                    ->formatStateUsing(fn ($record) =>
                        $record->complements_fcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné'
                    )
                    ->badge()
                    ->color(fn ($record) => $record->complements_fcm?->donnees_du_parcours ? 'success' : 'gray')
                    ->icon(fn ($record) => $record->complements_fcm?->donnees_du_parcours ? 'heroicon-s-book-open' : 'heroicon-s-plus')
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
                            ->visible(fn ($record) => $record->estEnFcm() && static::canAccessPermission('attribuer.parcours'))
                    ),

                Tables\Columns\TextColumn::make('complements_fcm.donnees_du_parcours.taux_global')
                    ->label('Taux')
                    ->sortable()
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
                    ->tooltip(fn (int $state): string => $state > 0 ? "$state activité(s) en attente de validation" : 'Aucune activité en attente'),
            ])
            
            ->filters([
                Filter::make('en_fcm')
                    ->form([Toggle::make('en_fcm')->label('Marins en FCM')->default(true)])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['en_fcm'] ?? false, fn ($q) => $q->enFcm())
                    ),
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

                   
                // USERS
                Tables\Actions\Action::make('associer_user')
    ->icon(fn ($record) => $record->user ? 'heroicon-m-pencil-square' : 'heroicon-m-user-plus')
    ->color(fn ($record) => $record->user ? 'warning' : 'success')
    ->iconButton()
    ->visible(fn ($record) =>
        static::canAccessAnyPermission(['admin'])
    )
    ->form(fn ($record) => $record->user
        ? [
            // Forms\Components\Placeholder::make('roles_info')
            // ->label('Rôles Actuels')
            // ->content(function ($record){
            //     $roles = $record->user?->roles->pluck('name')->toArray() ?? [];
            //     return empty($roles)
            //     ? 'Aucun role assigné'
            //     : implode(', ', array_map(fn($r) => str_replace('fcmcentral::','', $r), $roles));
            // }),
            
            
            Forms\Components\TextInput::make('password')
                ->label('Nouveau mot de passe')
                ->password()
                ->minLength(8)
                ->maxLength(100)
                ->helperText('Laissez vide pour conserver le mot de passe actuel')
                ->dehydrated(fn ($state) => filled($state)), // ✅ Ne soumettre que si rempli
            // Forms\Components\Select::make('roles')
            //     ->label('Rôles')
            //     ->multiple()
            //     ->options(
            //         \Spatie\Permission\Models\Role::query()
            //             ->where('guard_name', 'web')
            //             ->where('name', 'like', 'fcmcentral::%')
            //             ->pluck('name', 'name') // ✅ name => name (pas id)
            //     )
            //     ->searchable()
            //     ->preload()
            //     ->default(fn ($record) => $record->user?->roles->pluck('name')->toArray() ?? []) // ✅ pluck('name')
        ]
        : [
            Forms\Components\Placeholder::make('email')
                ->label('Email')
                ->content($record->email),
            Forms\Components\TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->minLength(8)
                ->maxLength(100)
                ->required(),
            // Forms\Components\Select::make('roles')
            //     ->label('Rôles')
            //     ->multiple()
            //     ->options(
            //         \Modules\FcmCentral\Models\Role::query()
            //             ->where('guard_name', 'web')
            //             ->where('name', 'like', 'fcmcentral::%')
            //             ->pluck('name', 'name') // ✅ name => name
            //     )
            //     ->searchable()
            //     ->preload()
            //     ->required(),
        ]
    )
    ->action(function ($record, array $data) {
        $user = $record->user;
       
        if (!$user) {
            // ✅ CRÉATION DU USER
            $user = \Modules\RH\Models\User::create([
                'email' => $record->email,
                'display_name' => $record->prenom . ' ' . $record->nom,
                'password' => $data['password'], // ✅ bcrypt() dans le mutateur
                'admin' => false,
                'uuid' => \Illuminate\Support\Str::uuid(), // ✅ Si nécessaire
            ]);
           
            $record->user_id = $user->id;
            $record->save();
        } else {
            // ✅ MISE À JOUR DU USER (uniquement si mot de passe fourni)
            $updateData = [];
           
            if (!empty($data['password'])) {
                $updateData['password'] = $data['password']; // ✅ bcrypt() dans le mutateur
            }
           
            if (!empty($updateData)) {
                $user->update($updateData);
            }
        }
       
        // ✅ SYNCHRONISATION DES RÔLES
        // if (isset($data['roles']) && is_array($data['roles'])) {
        //    // $user->syncRoles($data['roles'],'web');
        //    $user->roles()->detach();
        //    foreach ($data['roles'] as $roleName){
        //     $user->assignRole($roleName);
        //    }
        // }
        // if (isset($data['roles']) && is_array($data['roles'])) {
        //     // Utilise le modèle Role de ton module
        //     $user->roles()->detach();
        
        //     foreach ($data['roles'] as $roleName) {
        //         $role = \Modules\FcmCentral\Models\Role::where('name', $roleName)
        //             ->where('guard_name', 'web')
        //             ->first(); 
        //         if ($role) {
        //             $user->assignRole($role); // assignRole accepte un objet Role
        //         }
        //     }
        // }
        
        
       
        \Filament\Notifications\Notification::make()
            ->title('Compte utilisateur mis à jour')
            ->body('Le compte utilisateur et les rôles ont été mis à jour.')
            ->success()
            ->send();
    })
    ->modalHeading(fn ($record) => $record->user ? 'Modifier le compte utilisateur' : 'Créer le compte utilisateur')
    ->modalSubmitActionLabel(fn ($record) => $record->user ? 'Mettre à jour' : 'Créer')
    ->modalCancelActionLabel('Annuler'),

                    

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
                    ->visible(fn ($record) => static::canAccessPermission('livret.voir') && $record->complements_fcm?->donnees_du_parcours)
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
                        ->visible(function ($record) {
                            return static::canAccessPermission('livret.voir')
                            && $record->complements_fcm
                            && $record->complements_fcm->donnees_du_parcours;
                        })
                        ->action(function ($record){
                            $url = static::getUrl('fiche-bilan', ['record' => $record->id]);
                            return redirect($url);
                        }),

                    
                    // ICONE PDF
                    Tables\Actions\Action::make('livret-pdf')
    ->iconButton()
    ->icon('heroicon-m-document-text')
    ->color('secondary')
    ->extraAttributes([
        'title' => 'Télécharger PDF du Livret FCM',
        'class' => 'btn-pdf',
    ])
    ->visible(function ($record) {
        return static::canAccessPermission('livret.telecharger')
        && $record->complements_fcm
        && $record->complements_fcm->donnees_du_parcours;
;
    })
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
->visible(function ($record) {
    // Visible si le marin est suivi en FCM et que l'utilisateur peut créer des notes
    return static:: canAccessAnyPermission([
        'admin',
        'note.index',
        'permission.fps',
    'permission.bcf',
        
    ])
    && $record->complements_fcm
    && $record->complements_fcm->donnees_du_parcours;
})
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

    // Marin pré-sélectionné (caché)
    Forms\Components\Hidden::make('marin_id')
        ->default(fn ($record) => $record->id),

    // Affichage du marin sélectionné
    Forms\Components\Placeholder::make('marin_selected')
        ->label('Marin concerné')
        ->content(fn ($record) => $record->grade?->libelle_court . ' ' . $record->prenom . ' ' . $record->nom),

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
                ->label('code')
                ,
            Forms\Components\TextInput::make('libelle_court')
                ->required()
                ->label('Libellé court')
                ->maxLength(150),
            
        ])
        ->createOptionUsing(function (array $data) {
            return \Modules\FcmCentral\Models\TypeNote::create($data)->id;
        }),

    Forms\Components\DatePicker::make('date')
        ->label('Date')
        ->default(now())
        ->nullable(),

    // Rédacteur automatique selon le rôle
    // Forms\Components\Hidden::make('redacteur_id')
    //     ->default(function () {
    //         if (static::canAccessAnyPermission([
                
    //             'permission.bcf','admin'
               
    //         ])) {
    //             // Si c'est un mentor, utiliser son ID marin
    //             $currentMarin = Marin::fromCurrentUser();
    //             return $currentMarin ? $currentMarin->id : auth()->user()->id;
    //         }
    //         // Sinon utiliser l'ID utilisateur
    //         return auth()->user()->id;
    //     }),

    // Affichage du rédacteur
    // Forms\Components\Placeholder::make('redacteur_info')
        // ->label('Rédacteur')
        // ->content(function () {
        //     if (static::canAccessAnyPermission([
                
        //         'permission.bcf','admin'
                
        //     ])) {
        //         $currentMarin = Marin::fromCurrentUser();
        //         if ($currentMarin) {
        //             return $currentMarin->grade?->libelle_court . ' ' . $currentMarin->prenom . ' ' . $currentMarin->nom;
        //         }
        //     }
        //     return auth()->user()->name ?? 'Utilisateur connecté';
        // }),
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

       
    

    Forms\Components\Toggle::make('valide_bcf')
        ->label('Validé BCF')
        ->default(false)
        ->visible(fn () => static:: canAccessAnyPermission([
            'admin',
            'permission.bcf',  
    ])), // Seuls les admins peuvent cocher

    Forms\Components\TextInput::make('lien_url')
        ->label('Lien URL (optionnel)')
        ->url()
        ->nullable()
        ->placeholder('https://...'),

    // Champs cachés pour objet_type et objet_id
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

        // Créer la note
      
        $note = \Modules\FcmCentral\Models\Note::create([
            'libelle' => $data['libelle'],
            'rh_marin_id' => $record->id,
            //'marin_id' => $record->complementsFcm->id,
            'type_note_id' => $data['type_note_id'] ?? null,
            'date' => $data['date'] ?? now(),
            'redacteur_id' => $data['redacteur_id'],
            'valide_bcf' => $data['valide_bcf'] ?? false,
            'lien_url' => $data['lien_url'] ?? null,
            'objet_type' => 'marin',
            'objet_id' => $record->id,
        ]);

        // Notification de succès
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

        // Notification d'erreur
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
            if (!$marin->suiviEnFcm || !$marin->complements_fcm) {
                return 0;
            }

            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
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
