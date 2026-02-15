<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Carbon\Carbon;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Illuminate\Support\Facades\Response;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Fieldset;
use Modules\FcmCentral\Models\TypeEtat;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\EditAction;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCommun\Services\LivretPdfService;
use Filament\Forms\Components\Hidden;
use Log;
use Modules\FcmCentral\Models\Note;
use Exception;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;

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
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Marins';
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
           
            ->headerActions([
                   /** Telechargement CSV */
                   Action::make('downloadCsv')
                   ->label('Exporter les marins')
                   ->icon('heroicon-o-arrow-down-tray')
                   ->color('primary')
                   ->action(function () {
                       $marins = Marin::with([
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
                               $tempsPpa = $dateDebut ? Carbon::parse($dateDebut)->diffInDays(now()) : '0';
                               $tempsPpa =  round($tempsPpa,0);
                               
                               // PROGRESSION
                               $parcours = $marin->complementsFcm?->donnees_du_parcours?->parcoursmarin;
                               $progression = $parcours ? (new CalculHeuresParcoursService())->calculerPourcentageEtatValid($parcours) : null;
                               
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
                                   $gelFcm ? Carbon::parse($gelFcm)->format('d/m/Y') : 'Non renseigné',
                                   $vertPpa ? Carbon::parse($vertPpa)->format('d/m/Y') : 'Non renseigné',
                                   $tempsPpa ?? '',
                                   $progression ?? '',
                                   $stagesManquants,
                               ], ';');
                           }
                           fclose($file);
                       };
           
                       return Response::stream($callback, 200, [
                           "Content-Type" => "text/csv; charset=UTF-8",
                           "Content-Disposition" => "attachment; filename=marins-fcm.csv",
                       ]);
                   })
                   ->visible(fn() => auth()->check() && auth()->user()->can("fcmcentral::exporter_csv")),
           
                   /** Fin Telechargment */
                
                Action::make('recherche-annuaire')
                    ->label('Ajouter un marin')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->button()
                    ->url(fn() => RechercheAnnuairePage::getUrl())
                    ->visible(fn() => RechercheAnnuairePage::canAccess())
            ])
            ->columns([
                TextColumn::make('grade.libelle_court')->searchable(),
                TextColumn::make('unite.libelle_court')->searchable()->toggleable(),
                TextColumn::make('unite.lieuUnite.libelle_court')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nom')->searchable()->sortable(),
                TextColumn::make('prenom')->searchable()->label('Prénom'),
                
                // 🎯 FCM ACTIF (Oui/Non)
                // Colonne FCM avec gestion du typeEtat
                IconColumn::make('fcm_actif')
                    ->label('FCM')
                    ->boolean()
                    ->state(fn ($record) => $record->suivi_en_fcm && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'heroicon-c-shield-check' : 'heroicon-o-x-circle')
                    ->trueColor(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'gray' : 'success')
                    ->falseColor(fn ($record) => $record->complementsFcm?->getJalonFcmLibelle() === 'FIN' ? 'gray' : 'danger')
                    ->hidden(fn ($livewire): bool => method_exists($livewire, 'isFilterEnFcmActive') && $livewire->isFilterEnFcmActive())
                    ->action(
                        Action::make('toggle_fcm')
                            ->modalHeading(fn ($record) => $record->suivi_en_fcm ? 'Marin en FCM' : 'Activer FCM')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->requiresConfirmation()
                            ->schema(function ($record) {
                                if ($record->suivi_en_fcm && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN') {
                                }
                                
                                return [
                                    Select::make('cohorte_id')
                                        ->label("Choisir la cohorte")
                                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.cohorte'))
                                        ->options(Cohorte::query()->orderBy('libelle_long')->pluck('libelle_long', 'uuid')),
                                    Select::make('mentor_id')
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
                                    Select::make('parcours_id')
                                        ->label('Choisir un Parcours')
                                        ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                                        ->searchable()
                                        ->visible(fn() => auth()->user()->can('fcmcentral::attribuer.parcours'))
                                ];
                            })
                            ->action(function ($record, $data) {
                                
                                if ($record->estEnFcm() && $record->complementsFcm?->getJalonFcmLibelle() !== 'FIN') {
                                    Notification::make()
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
                TextColumn::make('type_jalon_fcm')
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
                                      
                        Action::make('jalons_fcm')
                            ->modalHeading('Modifier les Jalons FCM')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->modalWidth('3xl')
                            ->schema([
                                // ✅ SECTION JALONS FCM avec toggles
                                Section::make('Jalons FCM')
                                    ->schema([
                                        // DATE SÉDIMENTATION
                                        Toggle::make('modifier_date_sedimentation')
                                            ->label('Modifier la date de sédimentation')
                                            ->default(fn ($record) => !empty($record->complementsFcm?->date_sedimentation))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                                if (!$state) {
                                                    $set('date_sedimentation', null);
                                                } else {
                                                    $set('date_sedimentation', $record->complementsFcm?->date_sedimentation ?? now());
                                                }
                                            }),
                            
                                        DatePicker::make('date_sedimentation')
                                            ->label('Date de début d\'entrée en FCM')
                                            ->visible(fn (Get $get) => $get('modifier_date_sedimentation'))
                                            ->default(fn ($record) => $record->complementsFcm?->date_sedimentation),
                            
                                        // DATE FCM
                                        Toggle::make('modifier_date_fcm')
                                            ->label('Modifier la date de fin de sédimentation')
                                            ->default(fn ($record) => !empty($record->complementsFcm?->date_fcm))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                                if (!$state) {
                                                    $set('date_fcm', null);
                                                } else {
                                                    $set('date_fcm', $record->complementsFcm?->date_fcm ?? now());
                                                }
                                            }),
                            
                                        DatePicker::make('date_fcm')
                                            ->label('Date de fin de sédimentation')
                                            ->visible(fn (Get $get) => $get('modifier_date_fcm'))
                                            ->default(fn ($record) => $record->complementsFcm?->date_fcm),
                            
                                        // DATE COMPÉTENCE
                                        Toggle::make('modifier_date_competence')
                                            ->label('Modifier la date de vert compétence')
                                            ->default(fn ($record) => !empty($record->complementsFcm?->date_competence))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                                if (!$state) {
                                                    $set('date_competence', null);
                                                } else {
                                                    $set('date_competence', $record->complementsFcm?->date_competence ?? now());
                                                }
                                            }),
                            
                                        DatePicker::make('date_competence')
                                            ->label('Date de vert compétence')
                                            ->visible(fn (Get $get) => $get('modifier_date_competence'))
                                            ->default(fn ($record) => $record->complementsFcm?->date_competence),
                                    ])
                                    ->columns(1),
                            
                                // ✅ SECTION GEL avec toggle
                                Section::make('Gestion du Gel')
                                    ->description(function ($record) {
                                        $gelEnCours = $record->gelActif();
                                        if ($gelEnCours) {
                                            $debut = $gelEnCours->date_debut->format('d/m/Y');
                                            $type = $gelEnCours->typeEtat?->libelle_court ?? '';
                                            return "🔴 **Marin en gel** {$type} depuis le {$debut}";
                                        }
                                        return '🟢 Marin non gelé';
                                    })
                                    ->schema([
                                        // ✅ TOGGLE POUR CRÉER/CLÔTURER GEL
                                        Toggle::make('action_gel')
                                            ->label(fn ($record) => $record->gelActif() ? 'Clôturer le gel en cours' : 'Créer un nouveau gel')
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                                if ($state) {
                                                    $gelActif = $record->gelActif();
                                                    if ($gelActif) {
                                                        // Mode clôture
                                                        $set('date_fin_gel', now()->format('Y-m-d'));
                                                    } else {
                                                        // Mode création
                                                        $set('date_debut_gel', now()->format('Y-m-d'));
                                                        $set('type_gel_id', null);
                                                        $set('motif_gel', '');
                                                    }
                                                } else {
                                                    // Reset
                                                    $set('date_debut_gel', null);
                                                    $set('date_fin_gel', null);
                                                    $set('type_gel_id', null);
                                                    $set('motif_gel', '');
                                                }
                                            }),
                            
                                        // ✅ CRÉATION D'UN NOUVEAU GEL
                                        Fieldset::make('Nouveau gel')
                                            ->visible(fn (Get $get, $record) => $get('action_gel') && !$record->gelActif())
                                            ->schema([
                                                Select::make('type_gel_id')
                                                    ->label('Type de gel')
                                                    ->options(function () {
                                                        return TypeEtat::pluck('libelle_court', 'id');
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('libelle_court')
                                                            ->required()
                                                            ->label('Libellé')
                                                            ->maxLength(150),
                                                        TextInput::make('code')
                                                            ->required()
                                                            ->label('Code')
                                                            ->maxLength(50),
                                                    ])
                                                    ->createOptionUsing(fn (array $data) => TypeEtat::create($data)->id),
                            
                                                DatePicker::make('date_debut_gel')
                                                    ->label('Date de début du gel')
                                                    ->required()
                                                    ->default(now()),
                            
                                                Textarea::make('motif_gel')
                                                    ->label('Motif du gel')
                                                    ->rows(3),
                                            ]),
                            
                                        // ✅ CLÔTURE DU GEL EN COURS
                                        Fieldset::make('Clôture du gel')
                                            ->visible(fn (Get $get, $record) => $get('action_gel') && $record->gelActif() !== null)
                                            ->schema([
                                                Placeholder::make('info_gel_actif')
                                                    ->label('Gel actif')
                                                    ->content(function ($record) {
                                                        $gel = $record->gelActif();
                                                        if (!$gel) return '';
                                                        
                                                        $debut = $gel->date_debut->format('d/m/Y');
                                                        $type = $gel->typeEtat?->libelle_court ?? 'Gel';
                                                        $motif = $gel->libelle ? " - {$gel->libelle}" : '';
                                                        
                                                        return "**{$type}** depuis le {$debut}{$motif}";
                                                    }),
                            
                                                DatePicker::make('date_fin_gel')
                                                    ->label('Date de fin du gel')
                                                    ->required()
                                                    ->default(now()),
                                            ]),
                            
                                        // ✅ HISTORIQUE
                                        // Forms\Components\Placeholder::make('historique')
                                        //     ->label('Historique des gels')
                                        //     ->content(function ($record) {
                                        //         $gels = $record->etats()->orderBy('date_debut', 'desc')->get();
                                                
                                        //         if ($gels->isEmpty()) {
                                        //             return '✅ Aucun gel enregistré';
                                        //         }
                            
                                        //         $html = '<div class="space-y-2 mt-2">';
                                        //         foreach ($gels as $gel) {
                                        //             $actif = $gel->date_fin ? '⚫ Terminé' : '🔴 En cours';
                                        //             $debut = $gel->date_debut?->format('d/m/Y') ?? '-';
                                        //             $fin = $gel->date_fin?->format('d/m/Y') ?? '-';
                                        //             $type = $gel->typeEtat?->libelle_court ?? 'Gel';
                                                    
                                        //             $html .= "<div class='p-2 bg-gray-100 dark:bg-gray-800 rounded text-sm'>";
                                        //             $html .= "<strong>{$actif}</strong> {$type}<br>";
                                        //             $html .= "Du <strong>{$debut}</strong> au {$fin}";
                                        //             if ($gel->libelle) {
                                        //                 $html .= "<br><span class='text-gray-600 dark:text-gray-400 text-xs italic'>{$gel->libelle}</span>";
                                        //             }
                                        //             $html .= "</div>";
                                        //         }
                                        //         $html .= '</div>';
                            
                                        //         return new \Illuminate\Support\HtmlString($html);
                                        //     })
                                        //     ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),
                            ])
                            ->action(function ($record, array $data) {
                               
                                // DATE SÉDIMENTATION
                                if (array_key_exists('modifier_date_sedimentation', $data)) {
                                    if ($data['modifier_date_sedimentation']) {
                                        // Toggle ON : envoyer la date
                                        $dateValue = $data['date_sedimentation'] ?? null;
                                    } else {
                                        // Toggle OFF : envoyer null pour effacer
                                        $dateValue = null;
                                    }
                                    
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_sedimentation" => $dateValue]
                                    ));
                                }
                                
                                // DATE FCM
                                if (array_key_exists('modifier_date_fcm', $data)) {
                                    if ($data['modifier_date_fcm']) {
                                        // Toggle ON : envoyer la date
                                        $dateValue = $data['date_fcm'] ?? null;
                                    } else {
                                        // Toggle OFF : envoyer null pour effacer
                                        $dateValue = null;
                                    }
                                    
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_fcm" => $dateValue]
                                    ));
                                }
                                
                                // DATE COMPÉTENCE
                                if (array_key_exists('modifier_date_competence', $data)) {
                                    if ($data['modifier_date_competence']) {
                                        // Toggle ON : envoyer la date
                                        $dateValue = $data['date_competence'] ?? null;
                                    } else {
                                        // Toggle OFF : envoyer null pour effacer
                                        $dateValue = null;
                                    }
                                    
                                    event(new UserGeneratedEvent(
                                        UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                                        auth()->user()->uuid,
                                        ObjectClasses::MARIN,
                                        $record->uuid,
                                        ["date_competence" => $dateValue]
                                    ));
                                }


                            
                                // ✅ GESTION DU GEL (seulement si toggle activé)
                                if (!empty($data['action_gel'])) {
                                    $gelEnCours = $record->gelActif();
                            
                                    // CRÉER UN NOUVEAU GEL
                                    if (!$gelEnCours && !empty($data['date_debut_gel']) && !empty($data['type_gel_id'])) {
                                        $record->etats()->create([
                                            'type_etat_id' => $data['type_gel_id'],
                                            'libelle' => $data['motif_gel'] ?? '',
                                            'date_debut' => $data['date_debut_gel'],
                                            'date_fin' => null,
                                        ]);
                                        
                                        Notification::make()
                                            ->title('✅ Gel créé')
                                            ->success()
                                            ->send();
                                    }
                                    
                                    // CLÔTURER LE GEL EN COURS
                                    elseif ($gelEnCours && !empty($data['date_fin_gel'])) {
                                        $gelEnCours->update([
                                            'date_fin' => $data['date_fin_gel'],
                                        ]);
                                        
                                        Notification::make()
                                            ->title('✅ Gel clôturé')
                                            ->success()
                                            ->send();
                                    }
                                }
                            
                                
                            })
                            
                            ->visible(fn ($record) =>
                                $record->suivi_en_fcm
                                && auth()->check()
                                && auth()->user()->can("fcmcentral::definir_jalons_de_fcm")
                            )
                            
                ),


                TextColumn::make('complementsFcm.cohorte.libelle_court')
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
                TextColumn::make('complementsFcm.mentor.nom')
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
                        Action::make('gerer_mentor')
                            ->modalHeading('Gestion du mentor')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->schema([
                                Select::make('mentor_id')
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
                TextColumn::make('complementsFcm.donnees_du_parcours.parcours_serialise.libelle_court')
                    ->label('Parcours')
                    ->formatStateUsing(fn ($record) =>
                        $record->complementsFcm?->donnees_du_parcours?->parcours_serialise?->libelle_court ?? 'Non assigné'
                    )
                    ->badge()
                    ->color(fn ($record) => $record->complementsFcm?->donnees_du_parcours ? 'success' : 'gray')
                    ->icon(fn ($record) => $record->complementsFcm?->donnees_du_parcours ? 'heroicon-s-book-open' : 'heroicon-s-plus')
                    ->placeholder('Non assigné')
                    ->action(
                        Action::make('gerer_parcours')
                            ->modalHeading('Gestion du parcours')
                            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
                            ->schema([
                                Select::make('parcours_id')
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

                TextColumn::make('complementsFcm.donnees_du_parcours.taux_global')
                    ->label('Taux')
                    //->sortable()
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => intval(round($state)))
                    ->placeholder('0'),

                // 🔔 ACTIVITÉS EN ATTENTE
                TextColumn::make('activites_en_attente')
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
                ->schema([
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

            ->recordActions([

                //EDIT
                EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn ($record) => static::canEdit($record)),
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
                    ->visible(fn ($record) => $record->complementsFcm?->donnees_du_parcours &&
                        auth()->check() &&
                        auth()->user()->can("fcmcentral::livret.voir"))
                    ->url(fn ($record) => static::getUrl('livret-de-fcm', ['record' => $record->id])),

                // FICHE BILAN
                Action::make('fiche-bilan')
                    ->iconButton()
                    ->icon('heroicon-m-chart-bar-square')
                    ->color('warning')
                    ->extraAttributes([
                        'title' => 'Fiche Bilan FCM',
                        'class' => 'btn-bilan',
                    ])
                    ->visible(fn ($record) => FicheBilan::canAccess(["record" => $record]))
                    ->url(fn($record) => FicheBilan::getUrl(['record' => $record->id])),
                // ICONE PDF
                Action::make('livret-pdf')
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
                            echo app(LivretPdfService::class)->generatePdf($record);
                        }, "livret-fcm-{$record->nom}-{$record->prenom}.pdf", [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),

                    // ✅ NOUVEAU BOUTON NOTE
                Action::make('create-note')
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
                ->schema([
                    Textarea::make('libelle')
                        ->required()
                        ->label('Contenu de la note')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('Saisissez le contenu de votre note...'),
                
                    Hidden::make('marin_id')
                        ->default(fn ($record) => $record->id),
                
                    Placeholder::make('marin_selected')
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
                
                    DatePicker::make('date')
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
                
                    TextInput::make('lien_url')
                        ->label('Lien URL (optionnel)')
                        ->url()
                        ->nullable()
                        ->placeholder('https://...'),
                
                    Hidden::make('objet_type')
                        ->default('marin'),
                
                    Hidden::make('objet_id')
                        ->default(fn ($record) => $record->id),
                ])
                ->action(function ($record, array $data) {
                    try {
                        Log::info('Tentative de création de note', [
                            'record_id' => $record->id,
                            'data' => $data,
                        ]);
                
                        // Détermination du rédacteur (user connecté)
                        $currentMarin = Marin::fromCurrentUser();
                       // $redacteurId =  auth()->user()->id;
                      
                       $redacteur = auth()->user()->display_name;
                      // dd( $redacteur);
                        // Créer la note
                        $note = Note::create([
                            'libelle' => $data['libelle'],
                            'rh_marin_id' => $record->id,
                            //'marin_id' => $record->complementsFcm->id,
                            // 'type_note_id' => $data['type_note_id'] ?? null, // Champ commenté
                            'date' => $data['date'] ?? now(),
                            //'redacteur_id' => $redacteurId, // Utilisateur connecté
                            'redacteur' => $redacteur,
                            'valide_bcf' => false, // Champ commenté mais valeur prise en compte
                            'lien_url' => $data['lien_url'] ?? null,
                            'objet_type' => 'marin',
                            'objet_id' => $record->id,
                        ]);
                
                        Notification::make()
                            ->title('Note créée avec succès')
                            ->body("La note pour {$record->prenom} {$record->nom} a été créée.")
                            ->success()
                            ->send();
                
                    } catch (Exception $e) {
                        Log::error('Erreur lors de la création de la note', [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                            'record_id' => $record->id,
                            'data' => $data,
                        ]);
                
                        Notification::make()
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
        } catch (Exception $e) {
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
