<?php

namespace Modules\FcmCentral\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\FcmCentral\Database\Factories\MarinFactory;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCommun\Models\Marin as BaseMarin;
use Modules\RH\Models\Marin as RHMarin;

/**
 * Modèle Marin pour le module FCM Central.
 *
 * Ce modèle étend le modèle de base et ajoute les fonctionnalités spécifiques au FCM.
 * Il permet de gérer les marins, leurs mentors, cohortes, et parcours de formation.
 *
 * EXEMPLES D'UTILISATION :
 *
 * // Récupérer un marin et vérifier son statut FCM
 * $marin = Marin::find(1);
 * if ($marin->estEnFcm()) {
 *     echo "Le marin {$marin->fullNameAndGrade} est suivi en FCM";
 * }
 *
 * // Vérifier si c'est un mentor
 * if ($marin->isMentor()) {
 *     $supervises = $marin->marinsSupervises();
 *     echo "Ce mentor supervise {$marin->nombreMarinsSupervises()} marins";
 * }
 *
 * // Accéder aux informations FCM
 * $cohorte = $marin->getCohorte();
 * $mentor = $marin->getMentor();
 * $grade = $marin->getGrade();
 * $unite = $marin->getUnite();
 */
class Marin extends BaseMarin
{
    use HasFactory;

    public static function findByEventReference(?string $reference): ?self
    {
        if (null === $reference) {
            return null;
        }

        return static::where('nid', $reference)->first()
            ?? static::where('uuid', $reference)->first()
            ?? (ctype_digit($reference) ? static::find($reference) : null);
    }

    protected static function newFactory(): MarinFactory
    {
        return MarinFactory::new();
    }

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Relation vers les compléments FCM (peut avoir plusieurs lignes).
     *
     * USAGE : Accès technique, rarement utilisé directement
     * EXEMPLE :
     * $lignes = $marin->lignes_dans_fcm_marin; // Collection de FcmMarin
     * foreach ($lignes as $ligne) {
     *     echo $ligne->cohorte->nom;
     * }
     */
    public function lignes_dans_fcm_marin()
    {
        return $this->hasMany(FcmMarin::class, 'rh_marin_id', 'id');
    }

    /**
     * Relation pour accéder aux compléments FCM (première ligne seulement).
     *
     * USAGE : Accès direct aux données FCM du marin
     * EXEMPLE :
     * $fcmData = $marin->complementsFcm;
     * echo $fcmData->cohorte->nom;
     * echo $fcmData->mentor->fullNameAndGrade;
     */
    public function complementsFcm()
    {
        return $this->hasOne(FcmMarin::class, 'rh_marin_id', 'id');
    }

    // ========================================
    // ATTRIBUTS CALCULÉS
    // ========================================

    /**
     * Attribut pour accéder facilement aux compléments FCM.
     *
     * USAGE : Syntaxe simplifiée pour accéder aux données FCM
     * EXEMPLE :
     * $marin->complements_fcm->cohorte->nom
     * $marin->complements_fcm->mentor->nom
     * $marin->complements_fcm->donnees_du_parcours->taux_global
     */
    public function getComplementsFcmAttribute()
    {
        return $this->lignes_dans_fcm_marin->first();
    }

    /**
     * Vérifie si le marin est suivi en FCM.
     *
     * USAGE : Attribut calculé pour vérification rapide
     * EXEMPLE :
     * if ($marin->suivi_en_fcm) {
     *     // Afficher les fonctionnalités FCM
     * }
     */
    public function getSuiviEnFcmAttribute(): bool
    {
        return $this->complementsFcm()->exists();
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    /**
     * Vérifie si ce marin est suivi en FCM.
     *
     * CAS D'USAGE :
     * - Conditionner l'affichage de fonctionnalités FCM
     * - Contrôler l'accès aux parcours de formation
     * - Statistiques sur les effectifs FCM
     *
     * EXEMPLES :
     * // Dans un contrôleur
     * if (!$marin->estEnFcm()) {
     *     return redirect()->back()->with('error', 'Ce marin n\'est pas suivi en FCM');
     * }
     *
     * // Dans une vue Blade
     *
     * @if($marin->estEnFcm())
     *     <div class="fcm-widgets">...</div>
     *
     * @endif
     *
     * // Pour des statistiques
     * $totalFcm = Marin::whereHas('complementsFcm')->count();
     */
    public function estEnFcm(): bool
    {
        return $this->complementsFcm()->exists();
    }

    /**
     * Vérifie si ce marin est un mentor (supervise d'autres marins).
     *
     * CAS D'USAGE :
     * - Afficher le tableau de bord mentor
     * - Contrôler l'accès aux fonctionnalités de supervision
     * - Attribution de permissions spécifiques
     * - Affichage de listes de marins supervisés
     *
     * EXEMPLES :
     * // Dans un middleware
     * if (!$marin->estMentor()) {
     *     abort(403, 'Accès réservé aux mentors');
     * }
     *
     * // Dans Filament
     * public static function canAccess(): bool {
     *     return auth()->user()->marin?->estMentor() ?? false;
     * }
     *
     * // Pour navigation conditionnelle
     * if ($marin->estMentor()) {
     *     $menu->add('Mes marins supervisés');
     * }
     */
    public function isMentor(): bool
    {
        return FcmMarin::whereHas('mentors', fn ($q) => $q->where('marin_id', $this->id))->exists();
    }

    /**
     * Retourne le grade du marin.
     *
     * CAS D'USAGE :
     * - Affichage dans les listes
     * - Tri par grade
     * - Contrôles d'accès basés sur le grade
     * - Génération de rapports
     *
     * EXEMPLES :
     * // Affichage simple
     * echo "Grade : " . $marin->getGrade();
     *
     * // Contrôle d'accès
     * $gradesAutorises = ['CV', 'CC', 'CF'];
     * if (in_array($marin->getGrade(), $gradesAutorises)) {
     *     // Accès administrateur
     * }
     *
     * // Dans une table Filament
     * Tables\Columns\TextColumn::make('grade')
     *     ->getStateUsing(fn($record) => $record->getGrade())
     */
    public function getGrade(): ?string
    {
        return $this->grade?->libelle_court ?? $this->grade?->libelle_long;
    }

    /**
     * Retourne l'unité du marin.
     *
     * CAS D'USAGE :
     * - Filtrage par unité
     * - Statistiques par base/navire
     * - Organisation hiérarchique
     * - Rapports de formation par unité
     *
     * EXEMPLES :
     * // Filtrage dans une liste
     * if (str_contains($marin->getUnite(), 'BREST')) {
     *     // Marins de Brest
     * }
     *
     * // Groupement pour statistiques
     * $marinsParUnite = Marin::all()->groupBy(fn($m) => $m->getUnite());
     *
     * // Dans un scope
     * Marin::dansUnite('TOULON')->get();
     */
    public function getUniteAsString(): ?string
    {
        return $this->unite?->libelle_court ?? $this->unite?->libelle_long;
    }

    /**
     * Retourne la cohorte FCM du marin.
     *
     * CAS D'USAGE :
     * - Affichage de la promotion
     * - Filtrage par cohorte
     * - Suivi de progression par groupe
     * - Planification des formations
     *
     * EXEMPLES :
     * $cohorte = $marin->getCohorte();
     * if ($cohorte) {
     *     echo "Cohorte : {$cohorte->libelle_court}";
     *     echo "Début : {$cohorte->date_debut}";
     * }
     *
     * // Pour un planning
     * foreach (Cohorte::all() as $cohorte) {
     *     $marins = Marin::cohorte($cohorte)->get();
     *     echo "{$cohorte->nom} : {$marins->count()} marins";
     * }
     */
    public function getCohorte(): ?Cohorte
    {
        return $this->complementsFcm?->cohorte;
    }

    /**
     * Retourne le mentor du marin.
     *
     * CAS D'USAGE :
     * - Affichage du mentor attribué
     * - Contact direct avec le mentor
     * - Suivi hiérarchique
     * - Notifications mentor-marin
     *
     * EXEMPLES :
     * $mentor = $marin->getMentor();
     * if ($mentor) {
     *     echo "Mentor : {$mentor->fullNameAndGrade}";
     *     // Envoyer une notification au mentor
     *     $mentor->notify(new MarinProgressionNotification($marin));
     * }
     *
     * // Dans une vue
     * <div class="mentor-info">
     *     <h4>Votre mentor</h4>
     *     <p>{{ $marin->getMentor()?->fullNameAndGrade ?? 'Aucun mentor attribué' }}</p>
     * </div>
     */
    public function getMentors(): \Illuminate\Support\Collection
    {
        return $this->complementsFcm?->mentors ?? collect();
    }

    /**
     * Récupère les marins supervisés par ce mentor.
     *
     * CAS D'USAGE :
     * - Tableau de bord mentor
     * - Liste des marins à suivre
     * - Calcul de charge de travail mentor
     * - Rapports de supervision
     *
     * EXEMPLES :
     * // Tableau de bord mentor
     * $supervises = $mentor->marinsSupervises();
     * foreach ($supervises as $marin) {
     *     echo "{$marin->fullNameAndGrade} - Progression: {$marin->complements_fcm->taux_global}%";
     * }
     *
     * // Filtrage par progression
     * $enRetard = $mentor->marinsSupervises()->filter(function($marin) {
     *     return $marin->complements_fcm->taux_global < 50;
     * });
     *
     * // Dans Filament Resource
     * public function marinsTable(): Table {
     *     return $this->marinsSupervises()->toTable();
     * }
     */
    public function marinsSupervises()
    {
        if (!$this->isMentor()) {
            return collect();
        }

        return static::mentorePar($this)->get();
    }

    /**
     * Compte le nombre de marins supervisés.
     *
     * CAS D'USAGE :
     * - Affichage rapide de la charge mentor
     * - Équilibrage des attributions
     * - Statistiques mentors
     * - Limitations sur le nombre de supervisés
     *
     * EXEMPLES :
     * // Vérification avant attribution
     * if ($mentor->nombreMarinsSupervises() >= 10) {
     *     throw new Exception('Ce mentor supervise déjà trop de marins');
     * }
     *
     * // Widget dashboard
     * <div class="stat-card">
     *     <h3>{{ $mentor->nombreMarinsSupervises() }}</h3>
     *     <p>Marins supervisés</p>
     * </div>
     *
     * // Répartition équilibrée
     * $mentorLeMoinsCarge = Marin::mentor()
     *     ->get()
     *     ->sortBy(fn($m) => $m->nombreMarinsSupervises())
     *     ->first();
     */
    public function nombreMarinsSupervises(): int
    {
        return FcmMarin::whereHas('mentors', fn ($q) => $q->where('marin_id', $this->id))->count();
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Méthode statique pour vérifier si un marin est mentor.
     *
     * CAS D'USAGE :
     * - Vérification rapide sans instancier le modèle complet
     * - Utilisation dans des callbacks ou closures
     * - Vérifications de permissions
     *
     * EXEMPLES :
     * // Dans un middleware
     * if (!Marin::estMentor(auth()->user()->marin)) {
     *     abort(403);
     * }
     *
     * // Dans une collection
     * $mentors = $marins->filter(fn($m) => Marin::estMentor($m));
     *
     * // Vérification de sécurité
     * if (Marin::estMentor($marinCible)) {
     *     // Autoriser certaines actions
     * }
     */
    public static function estMentor(?Model $marin): bool
    {
        if (!$marin) {
            return false;
        }

        return FcmMarin::whereHas('mentors', fn ($q) => $q->where('marin_id', $marin->id))->exists();
    }

    // ========================================
    // SCOPES (FILTRES DE REQUÊTE)
    // ========================================

    /**
     * Scope pour filtrer les marins en fonction de leur cohorte.
     *
     * CAS D'USAGE :
     * - Affichage des marins d'une promotion
     * - Statistiques par cohorte
     * - Planification des formations
     * - Suivi de progression par groupe
     *
     * EXEMPLES :
     * // Tous les marins de la cohorte 2024-01
     * $cohorte = Cohorte::where('libelle_court', '2024-01')->first();
     * $marins = Marin::cohorte($cohorte)->get();
     *
     * // Avec d'autres filtres
     * $marinsBrest = Marin::cohorte($cohorte)
     *     ->dansUnite('BREST')
     *     ->get();
     *
     * // Pagination
     * $marins = Marin::cohorte($cohorte)->paginate(20);
     *
     * // Comptage
     * $nombre = Marin::cohorte($cohorte)->count();
     */
    public function scopeCohorte(Builder $query, Cohorte $cohorte): void
    {
        $query->whereIn('id', function ($subQuery) use ($cohorte) {
            $subQuery->select('rh_marin_id')
                ->from('fcmcentral_fcm_marins')
                ->where('cohorte_id', $cohorte->id)
            ;
        });
    }

    /**
     * Scope pour filtrer les marins par mentor.
     *
     * CAS D'USAGE :
     * - Liste des marins supervisés par un mentor
     * - Rapports de supervision
     * - Notifications groupées
     * - Suivi de performance mentor
     *
     * EXEMPLES :
     * // Marins supervisés par un mentor spécifique
     * $mentor = Marin::find(5);
     * $supervises = Marin::mentorePar($mentor)->get();
     *
     * // Avec tri par progression
     * $supervises = Marin::mentorePar($mentor)
     *     ->with('complementsFcm')
     *     ->get()
     *     ->sortByDesc('complements_fcm.donnees_du_parcours.taux_global');
     *
     * // Marins en difficulté pour ce mentor
     * $enDifficulte = Marin::mentorePar($mentor)
     *     ->whereHas('complementsFcm.donnees_du_parcours', function($q) {
     *         $q->where('taux_global', '<', 50);
     *     })->get();
     */
    public function scopeMentorePar(Builder $query, ?Model $marin): void
    {
        if (!$marin) {
            $query->whereRaw('1 = 0'); // Requête vide

            return;
        }

        // Cast vers la classe courante si nécessaire
        if ($marin instanceof RHMarin || $marin instanceof BaseMarin) {
            $marin = cast_as_eloquent_descendant($marin, static::class);
        }

        $query->whereIn('id', function ($subQuery) use ($marin) {
            $subQuery->select('fcmcentral_fcm_marins.rh_marin_id')
                ->from('fcmcentral_fcm_marins')
                ->join('fcmcentral_fcm_marin_mentors', 'fcmcentral_fcm_marin_mentors.fcm_marin_id', '=', 'fcmcentral_fcm_marins.id')
                ->where('fcmcentral_fcm_marin_mentors.marin_id', $marin->id);
        });
    }

    /**
     * Scope pour filtrer les marins qui sont des mentors.
     *
     * CAS D'USAGE :
     * - Liste de tous les mentors
     * - Attribution d'un nouveau marin à un mentor
     * - Statistiques sur les mentors
     * - Gestion des mentors surchargés
     *
     * EXEMPLES :
     * // Tous les mentors
     * $mentors = Marin::mentor()->get();
     *
     * // Mentors disponibles (moins de 10 supervisés)
     * $mentorsDisponibles = Marin::mentor()
     *     ->get()
     *     ->filter(fn($m) => $m->nombreMarinsSupervises() < 10);
     *
     * // Pour un select Filament
     * Select::make('mentor_id')
     *     ->options(Marin::mentor()->pluck('full_name_and_grade', 'id'))
     *
     * // Mentor le moins chargé
     * $mentorOptimal = Marin::mentor()
     *     ->get()
     *     ->sortBy(fn($m) => $m->nombreMarinsSupervises())
     *     ->first();
     */
    public function scopeMentor(Builder $query): void
    {
        $query->whereIn('id', function ($subQuery) {
            $subQuery->select('marin_id')
                ->from('fcmcentral_fcm_marin_mentors')
                ->distinct();
        });
    }

    /**
     * Scope pour filtrer les marins suivis en FCM.
     *
     * CAS D'USAGE :
     * - Statistiques FCM globales
     * - Listes des effectifs FCM
     * - Rapports de formation
     * - Exclusion des marins non-FCM
     *
     * EXEMPLES :
     * // Total des marins FCM
     * $totalFcm = Marin::enFcm()->count();
     *
     * // Marins FCM par grade
     * $fcmParGrade = Marin::enFcm()
     *     ->with('grade')
     *     ->get()
     *     ->groupBy(fn($m) => $m->getGrade());
     *
     * // Marins FCM sans mentor
     * $sansMentor = Marin::enFcm()
     *     ->whereDoesntHave('complementsFcm', function($q) {
     *         $q->whereNotNull('mentor_id');
     *     })->get();
     */
    public function scopeEnFcm(Builder $query): void
    {
        // $query->whereIn('id', function($subQuery) {
        //     $subQuery->select('rh_marin_id')
        //              ->from('fcmcentral_fcm_marins');
        // });

        $query->whereHas('complementsFcm');
    }

    public function scopePasEnFcm(Builder $query): void
    {
        $query->whereNotIn('id', function ($subQuery) {
            $subQuery->select('rh_marin_id')
                ->from('fcmcentral_fcm_marins')
            ;
        });
    }

    /**
     * Scope pour filtrer par grade.
     *
     * CAS D'USAGE :
     * - Filtrage par niveau hiérarchique
     * - Statistiques par grade
     * - Contrôles d'accès basés sur le grade
     * - Rapports de formation par niveau
     *
     * EXEMPLES :
     * // Tous les officiers
     * $officiers = Marin::avecGrade('LV')->get();
     *
     * // Plusieurs grades
     * $gradesTom = ['CV', 'CC', 'CF'];
     * $marins = collect();
     * foreach ($gradesTom as $grade) {
     *     $marins = $marins->merge(Marin::avecGrade($grade)->get());
     * }
     *
     * // Avec d'autres filtres
     * $cvFcm = Marin::avecGrade('CV')->enFcm()->get();
     */
    public function scopeAvecGrade(Builder $query, string $grade): void
    {
        $query->whereHas('grade', function ($q) use ($grade) {
            $q->where('libelle_court', $grade)
                ->orWhere('libelle_long', $grade)
            ;
        });
    }

    /**
     * Scope pour filtrer par unité.
     *
     * CAS D'USAGE :
     * - Filtrage par base ou navire
     * - Statistiques par unité
     * - Organisation géographique
     * - Rapports par commandement
     *
     * EXEMPLES :
     * // Marins de Brest
     * $marinsBrest = Marin::dansUnite('BREST')->get();
     *
     * // Recherche partielle
     * $marinsCommando = Marin::dansUnite('COMMANDO')->get();
     *
     * // Combiné avec FCM
     * $fcmBrest = Marin::dansUnite('BREST')->enFcm()->get();
     *
     * // Pour un rapport
     * $unites = ['BREST', 'TOULON', 'CHERBOURG'];
     * foreach ($unites as $unite) {
     *     $count = Marin::dansUnite($unite)->enFcm()->count();
     *     echo "{$unite} : {$count} marins FCM";
     * }
     */
    public function scopeDansUnite(Builder $query, string $unite): void
    {
        $query->whereHas('unite', function ($q) use ($unite) {
            $q->where('libelle_court', 'LIKE', "%{$unite}%")
                ->orWhere('libelle_long', 'LIKE', "%{$unite}%")
            ;
        });
    }

    // ========================================
    // MÉTHODES D'INFORMATION
    // ========================================

    /**
     * Retourne un résumé des informations du marin.
     *
     * CAS D'USAGE :
     * - API REST pour applications mobiles
     * - Widgets de tableau de bord
     * - Exports de données
     * - Vues rapides d'information
     *
     * EXEMPLES :
     * // API endpoint
     * public function show(Marin $marin) {
     *     return response()->json($marin->getInfosResume());
     * }
     *
     * // Widget Filament
     * protected function getStats(): array {
     *     $marin = Marin::fromCurrentUser();
     *     return $marin->getInfosResume();
     * }
     *
     * // Vue Blade
     *
     * @php $infos = $marin->getInfosResume() @endphp
     * <div class="marin-summary">
     *     <h3>{{ $infos['nom_complet'] }}</h3>
     *     <p>Grade: {{ $infos['grade'] }}</p>
     *     <p>Unité: {{ $infos['unite'] }}</p>
     * </div>
     */
    public function getInfosResume(): array
    {
        return [
            'nom_complet' => $this->nom.' '.$this->prenom,
            'grade' => $this->getGrade(),
            'unite' => $this->getUnite(),
            'en_fcm' => $this->estEnFcm(),
            'est_mentor' => $this->isMentor(),
            'cohorte' => $this->getCohorte()?->libelle_court,
            'mentors' => $this->getMentors()->map(fn ($m) => $m->nom.' '.$m->prenom)->join(', '),
            'nb_supervises' => $this->nombreMarinsSupervises(),
        ];
    }

    /**
     * Retourne le nom complet avec grade.
     *
     * CAS D'USAGE :
     * - Affichage protocolaire
     * - Listes officielles
     * - Signatures de documents
     * - Mentions dans les rapports
     *
     * EXEMPLES :
     * // Dans une liste déroulante
     * Select::make('mentor_id')
     *     ->options(Marin::mentor()->pluck('full_name_and_grade', 'id'))
     *
     * // Dans un email
     * "Bonjour {$marin->fullNameAndGrade},"
     *
     * // Dans un rapport
     * "Formation dispensée par {$formateur->fullNameAndGrade}"
     *
     * // Signature électronique
     * $signature = "Validé par {$validateur->fullNameAndGrade} le " . now()->format('d/m/Y');
     */
    public function getFullNameAndGradeAttribute(): string
    {
        $grade = $this->getGrade();
        $nom = trim($this->prenom.' '.$this->nom);

        return $grade ? $grade.' '.$nom : $nom;
    }

    // ETAT
    // A SUPPRIMER
    public function getDernierTypeEtat()
    {
        // return $this->marin?->complementsFcm?->etats()->latest('date_debut')->first();
        return $this->etats()->latest('date_debut')->first();
    }

    public function getDernierTypeEtatCode()
    {
        return $this->getDernierTypeEtat()?->typeEtat?->code;
    }

    public function getDernierTypeEtatLibelle()
    {
        return $this->getDernierTypeEtat()?->typeEtat?->libelle_court;
    }

    public function getDernierTypeEtatId()
    {
        return $this->getDernierTypeEtat()?->typeEtat?->id;
    }

    public function historiserEtat(string $codeEtat, ?Carbon $dateDebut = null, ?Carbon $dateFin = null): void
    {
        $typeEtat = TypeEtat::where('code', $codeEtat)->firstOrFail();
        Etat::create([
            'uuid' => Str::uuid(),
            'rh_marin_id' => $this->id,
            'type_etat_id' => $typeEtat->id,
            'date_debut' => $dateDebut ?? now(),
            'date_fin' => $dateFin,
        ]);
    }

    // FIN A SUPPRIMER

    public function etats()
    {
        return $this->hasMany(Etat::class, 'rh_marin_id', 'id');
    }

    public function gelActif()
    {
        // Gel actif = date_fin null
        return $this->etats()->whereNull('date_fin')->first();
    }

    public function estGele(): bool
    {
        // Vérifie directement dans la table etats
        return $this->etats()->whereNull('date_fin')->exists();
    }

    public function historiqueGels()
    {
        return $this->etats()->orderBy('date_debut', 'desc')->get();
    }

    public function getEnGelAttribute(): bool
    {
        if (!$this->suivi_en_fcm) {
            return false;
        }

        // Faire la requete sur la table des gels/degels pour déterminer si la dernière entrée est un gel ou un degel
        // On peut mettre en cache le résultat.
        return false;
    }

    /**
     * Permet de faire $marin->est_mentor pour déterminer si un marin a d'autres marin placés sous son mentorat.
     */
    public function getEstMentorAttribute(): bool
    {
        return FcmMarin::whereHas('mentors', fn ($q) => $q->where('marin_id', $this->id))->exists();
    }

    /**
     * Déterminer si le Marin est le mentor d'un autre Marin ou pas.
     */
    public function est_mentor_de(Marin $marin): bool
    {
        if (!$this->est_mentor) {
            return false;
        }

        return FcmMarin::where('rh_marin_id', $marin->id)
            ->whereHas('mentors', fn ($q) => $q->where('marin_id', $this->id))
            ->exists();
    }

    /**
     * Renvoit la collection des Marins dont ce Marin est le mentor.
     */
    public function getMarinsMentoresAttribute()
    {
        if (!$this->est_mentor) {
            return collect();
        }

        return Marin::whereIn('id',
            FcmMarin::whereHas('mentors', fn ($q) => $q->where('marin_id', $this->id))->pluck('rh_marin_id')
        )->get();
    }

    /**
     * Raccourci vers le Parcours du marin via ses compléments FCM.
     */
    public function getParcoursAttribute(): ?Parcours
    {
        return $this->complementsFcm
            ?->donnees_du_parcours          // getDonneesDuParcoursAttribute → MarinParcours
            ?->parcours_serialise           // belongsTo ParcoursSerialise
            ?->parcoursModel;               // belongsTo Parcours (nom spécifique pour éviter le bug)
    }
}
