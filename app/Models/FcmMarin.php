<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Database\Factories\FcmMarinFactory;

/**
* Modèle FcmMarin - Table de liaison et compléments FCM
* 
 * Ce modèle représente les données spécifiques FCM d'un marin :
* - Cohorte d'appartenance
* - Mentor attribué
* - Données de progression
* - Parcours de formation
* 
 * EXEMPLES D'UTILISATION :
* 
 * // Création d'un suivi FCM
* $fcmMarin = FcmMarin::create([
*     'rh_marin_id' => $marin->id,
*     'cohorte_id' => $cohorte->id,
*     'mentor_id' => $mentor->id
* ]);
* 
 * // Vérifications
* if ($fcmMarin->hasMentor()) {
*     echo "Mentor : {$fcmMarin->mentor->fullNameAndGrade}";
* }
* 
 * // Progression
* echo "Progression globale : {$fcmMarin->getTauxGlobal()}%";
*/
class FcmMarin extends Model
{
    use HasTablePrefix, HasFactory;

    // ========================================
    // CONFIGURATION
    // ========================================

    protected $fillable = [
        'rh_marin_id',
        'data',
        'cohorte_id',
        'mentor_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Relation vers le marin RH
     * 
     * USAGE : Accès aux données de base du marin
     * EXEMPLE :
     * $fcmMarin = FcmMarin::find(1);
     * echo $fcmMarin->marin->nom . ' ' . $fcmMarin->marin->prenom;
     * echo $fcmMarin->marin->grade->libelle_court;
     */
    public function marin()
    {
        return $this->belongsTo(Marin::class, 'rh_marin_id');
    }

    /**
     * Relation vers la cohorte
     * 
     * USAGE : Accès aux informations de promotion
     * EXEMPLE :
     * $cohorte = $fcmMarin->cohorte;
     * echo "Cohorte : {$cohorte->libelle_court}";
     * echo "Début : {$cohorte->date_debut->format('d/m/Y')}";
     */
    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }

    /**
     * Relation vers le mentor
     * 
     * USAGE : Accès aux informations du mentor
     * EXEMPLE :
     * if ($fcmMarin->mentor) {
     *     echo "Mentor : {$fcmMarin->mentor->fullNameAndGrade}";
     *     // Envoyer notification au mentor
     *     $fcmMarin->mentor->notify(new ProgressionNotification($fcmMarin));
     * }
     */
    public function mentor()
    {
        return $this->belongsTo(Marin::class, 'mentor_id');
    }

    /**
     * Relation avec les parcours sérialisés
     * 
     * USAGE : Accès aux parcours de formation assignés
     * EXEMPLE :
     * foreach ($fcmMarin->parcoursSerialises as $parcours) {
     *     echo "Parcours : {$parcours->nom}";
     *     echo "Progression : {$parcours->pivot->taux_global}%";
     * }
     */
    public function parcoursSerialises(): BelongsToMany
    {
        return $this->belongsToMany(
            ParcoursSerialise::class, 
            'fcmcentral_marin_parcours', 
            'fcmmarin_id', 
            'parcoursserialise_id'
        )->withPivot('id', 'taux_global', 'taux_stage', 'taux_activite', 'parcoursmarin')
         ->withTimestamps();
    }

    /**
     * Relation vers les lignes de parcours marin
     * 
     * USAGE : Accès technique aux données de progression
     * EXEMPLE :
     * $progression = $fcmMarin->lignes_dans_marin_parcours->first();
     * echo "Taux global : {$progression->taux_global}%";
     */
    public function lignes_dans_marin_parcours()
    {
        return $this->hasMany(MarinParcours::class, 'fcmmarin_id');
    }

    // ========================================
    // ATTRIBUTS CALCULÉS
    // ========================================

    /**
     * Attribut pour accéder facilement aux données du parcours
     * 
     * USAGE : Syntaxe simplifiée pour les données de progression
     * EXEMPLE :
     * $fcmMarin->donnees_du_parcours->taux_global
     * $fcmMarin->donnees_du_parcours->taux_stage
     * $fcmMarin->donnees_du_parcours->parcoursmarin
     */
    public function getDonneesDuParcoursAttribute()
    {
        return $this->lignes_dans_marin_parcours->first();
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    /**
     * Vérifie si le marin a un mentor assigné
     * 
     * CAS D'USAGE :
     * - Validation avant certaines actions
     * - Affichage conditionnel d'interfaces
     * - Contrôles de workflow
     * - Statistiques sur l'attribution des mentors
     * 
     * EXEMPLES :
     * // Validation avant progression
     * if (!$fcmMarin->hasMentor()) {
     *     throw new Exception('Un mentor doit être attribué avant de commencer le parcours');
     * }
     * 
     * // Affichage conditionnel
     * @if($fcmMarin->hasMentor())
     *     <div class="mentor-section">...</div>
     * @else
     *     <div class="alert">Aucun mentor attribué</div>
     * @endif
     * 
     * // Statistiques
     * $avecMentor = FcmMarin::whereNotNull('mentor_id')->count();
     * $sansMentor = FcmMarin::whereNull('mentor_id')->count();
     */
    public function hasMentor(): bool
    {
        return !is_null($this->mentor_id);
    }

    /**
     * Vérifie si le marin a une cohorte assignée
     * 
     * CAS D'USAGE :
     * - Validation de complétude du dossier
     * - Planification des formations
     * - Groupements par promotion
     * - Contrôles avant inscription
     * 
     * EXEMPLES :
     * // Validation complétude
     * if (!$fcmMarin->hasCohorte()) {
     *     $errors[] = 'Cohorte non attribuée';
     * }
     * 
     * // Planification formations
     * $marinsPrets = FcmMarin::whereNotNull('cohorte_id')
     *     ->whereNotNull('mentor_id')
     *     ->get();
     * 
     * // Widget dashboard
     * $stats = [
     *     'avec_cohorte' => FcmMarin::whereNotNull('cohorte_id')->count(),
     *     'sans_cohorte' => FcmMarin::whereNull('cohorte_id')->count()
     * ];
     */
    public function hasCohorte(): bool
    {
        return !is_null($this->cohorte_id);
    }

    /**
     * Vérifie si le marin a un parcours assigné
     * 
     * CAS D'USAGE :
     * - Vérification avant affichage du livret
     * - Contrôle d'accès aux activités
     * - Validation de configuration
     * - Rapports de couverture parcours
     * 
     * EXEMPLES :
     * // Contrôle d'accès livret
     * if (!$fcmMarin->hasParcours()) {
     *     return redirect()->route('attente-parcours');
     * }
     * 
     * // Menu conditionnel
     * if ($fcmMarin->hasParcours()) {
     *     $menu->add('Mon livret FCM');
     * }
     * 
     * // Rapport de couverture
     * $avecParcours = FcmMarin::has('lignes_dans_marin_parcours')->count();
     * $pourcentage = ($avecParcours / FcmMarin::count()) * 100;
     */
    public function hasParcours(): bool
    {
        return $this->donnees_du_parcours !== null;
    }

    /**
     * Retourne le taux global de progression
     * 
     * CAS D'USAGE :
     * - Affichage de barres de progression
     * - Calculs de moyennes de cohorte
     * - Alertes sur retards
     * - Rapports de performance
     * 
     * EXEMPLES :
     * // Barre de progression
     * <div class="progress">
     *     <div class="progress-bar" style="width: {{ $fcmMarin->getTauxGlobal() }}%">
     *         {{ $fcmMarin->getTauxGlobal() }}%
     *     </div>
     * </div>
     * 
     * // Alerte retard
     * if ($fcmMarin->getTauxGlobal() < 30) {
     *     // Envoyer alerte au mentor
     * }
     * 
     * // Moyenne cohorte
     * $moyenneCohorte = $cohorte->fcmMarins->avg(fn($fm) => $fm->getTauxGlobal());
     */
    public function getTauxGlobal(): ?float
    {
        return $this->donnees_du_parcours?->taux_global;
    }

    /**
     * Retourne le taux de progression des stages
     * 
     * CAS D'USAGE :
     * - Suivi spécifique des stages
     * - Planification des sessions
     * - Rapports par type d'activité
     * - Identification des goulots d'étranglement
     * 
     * EXEMPLES :
     * // Suivi stages
     * if ($fcmMarin->getTauxStage() < 50) {
     *     echo "Besoin de programmer des stages";
     * }
     * 
     * // Rapport par type
     * $rapportStages = [
     *     'taux_stage' => $fcmMarin->getTauxStage(),
     *     'taux_activite' => $fcmMarin->getTauxActivite(),
     *     'ecart' => abs($fcmMarin->getTauxStage() - $fcmMarin->getTauxActivite())
     * ];
     */
    public function getTauxStage(): ?float
    {
        return $this->donnees_du_parcours?->taux_stage;
    }

    /**
     * Retourne le taux de progression des activités
     * 
     * CAS D'USAGE :
     * - Suivi des activités pratiques
     * - Équilibrage stage/activité
     * - Rapports détaillés
     * - Optimisation des parcours
     * 
     * EXEMPLES :
     * // Équilibrage
     * $ecartStageActivite = abs(
     *     $fcmMarin->getTauxStage() - $fcmMarin->getTauxActivite()
     * );
     * if ($ecartStageActivite > 20) {
     *     echo "Déséquilibre stage/activité détecté";
     * }
     */
    public function getTauxActivite(): ?float
    {
        return $this->donnees_du_parcours?->taux_activite;
    }

    // ========================================
    // MÉTHODES DE GESTION
    // ========================================

    /**
     * Assigne une cohorte
     * 
     * CAS D'USAGE :
     * - Attribution initiale
     * - Changement de promotion
     * - Gestion administrative
     * - Réorganisation des groupes
     * 
     * EXEMPLES :
     * // Attribution initiale
     * $cohorte = Cohorte::where('active', true)->first();
     * $fcmMarin->assignerCohorte($cohorte);
     * 
     * // Changement motivé
     * $nouvelleCohorte = Cohorte::find($request->cohorte_id);
     * $fcmMarin->assignerCohorte($nouvelleCohorte);
     * Log::info("Cohorte changée pour {$fcmMarin->marin->fullNameAndGrade}");
     * 
     * // Attribution en masse
     * foreach ($marinsAAffecter as $fcmMarin) {
     *     $fcmMarin->assignerCohorte($cohorteParDefaut);
     * }
     */
    public function assignerCohorte(Cohorte $cohorte): void
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
        $this->refresh();
        $this->load('cohorte');
    }

    /**
     * Assigne un mentor
     * 
     * CAS D'USAGE :
     * - Attribution initiale de mentor
     * - Changement de mentor (congés, mutation)
     * - Rééquilibrage des charges
     * - Remplacements temporaires
     * 
     * EXEMPLES :
     * // Attribution automatique (mentor le moins chargé)
     * $mentorOptimal = Marin::mentor()
     *     ->get()
     *     ->sortBy(fn($m) => $m->nombreMarinsSupervises())
     *     ->first();
     * $fcmMarin->assignerMentor($mentorOptimal);
     * 
     * // Changement de mentor
     * $nouveauMentor = Marin::find($request->mentor_id);
     * $fcmMarin->assignerMentor($nouveauMentor);
     * 
     * // Notification du nouveau mentor
     * $fcmMarin->mentor->notify(new NouveauMarinAssigneNotification($fcmMarin));
     */
    public function assignerMentor(Marin $mentor): void
    {
        $this->mentor_id = $mentor->id;
        $this->save();
        $this->refresh();
        $this->load('mentor');
    }

    /**
     * Retire le mentor
     * 
     * CAS D'USAGE :
     * - Départ/mutation du mentor
     * - Problème relationnel
     * - Réorganisation en attente
     * - Suspension temporaire
     * 
     * EXEMPLES :
     * // Départ du mentor
     * if ($mentor->isLeavingService()) {
     *     foreach ($mentor->marinsSupervises() as $marin) {
     *         $marin->complementsFcm->retirerMentor();
     *     }
     * }
     * 
     * // Suspension disciplinaire
     * if ($mentor->isSuspended()) {
     *     $marinsAReassigner = $mentor->marinsSupervises();
     *     foreach ($marinsAReassigner as $marin) {
     *         $marin->complementsFcm->retirerMentor();
     *         // Réassigner à un autre mentor
     *     }
     * }
     */
    public function retirerMentor(): void
    {
        $this->mentor_id = null;
        $this->save();
        $this->refresh();
        $this->load('mentor');
    }

    /**
     * Retire la cohorte
     * 
     * CAS D'USAGE :
     * - Réorganisation des promotions
     * - Erreur d'affectation
     * - Changement de statut
     * - Suspension du parcours
     * 
     * EXEMPLES :
     * // Réorganisation
     * if ($cohorte->isCancelled()) {
     *     foreach ($cohorte->fcmMarins as $fcmMarin) {
     *         $fcmMarin->retirerCohorte();
     *     }
     * }
     * 
     * // Correction d'erreur
     * if ($fcmMarin->cohorte->date_debut > $fcmMarin->marin->date_embarq) {
     *     $fcmMarin->retirerCohorte();
     *     // Réassigner à une cohorte appropriée
     * }
     */
    public function retirerCohorte(): void
    {
        $this->cohorte_id = null;
        $this->save();
        $this->refresh();
        $this->load('cohorte');
    }

    // ========================================
    // SETTERS PERSONNALISÉS
    // ========================================

    /**
     * Setter pour cohorte (utilise la méthode assignerCohorte)
     * 
     * USAGE : Syntaxe simplifiée d'attribution
     * EXEMPLE :
     * $fcmMarin->cohorte = $nouvelleCohorte; // Auto-save et refresh
     */
    public function setCohorteAttribute(Cohorte $cohorte)
    {
        $this->assignerCohorte($cohorte);
    }

    /**
     * Setter pour mentor (utilise la méthode assignerMentor)
     * 
     * USAGE : Syntaxe simplifiée d'attribution
     * EXEMPLE :
     * $fcmMarin->mentor = $nouveauMentor; // Auto-save et refresh
     */
    public function setMentorAttribute(Marin $mentor)
    {
        $this->assignerMentor($mentor);
    }

    // ========================================
    // FACTORY
    // ========================================

    protected static function newFactory()
    {
        return FcmMarinFactory::new();
    }

    // ========================================
    // SCOPES (FILTRES DE REQUÊTE)
    // ========================================

    /**
     * Scope pour filtrer par cohorte
     * 
     * CAS D'USAGE :
     * - Listes par promotion
     * - Rapports de cohorte
     * - Planification par groupe
     * - Statistiques par promotion
     * 
     * EXEMPLES :
     * // Tous les FCM d'une cohorte
     * $fcmMarins = FcmMarin::cohorte($cohorte)->get();
     * 
     * // Avec progression
     * $fcmMarins = FcmMarin::cohorte($cohorte)
     *     ->with('lignes_dans_marin_parcours')
     *     ->get();
     * 
     * // Moyenne de la cohorte
     * $moyenne = FcmMarin::cohorte($cohorte)
     *     ->whereHas('lignes_dans_marin_parcours')
     *     ->with('lignes_dans_marin_parcours')
     *     ->get()
     *     ->avg(fn($fm) => $fm->getTauxGlobal());
     */
    public function scopeCohorte(Builder $query, Cohorte $cohorte): void
    {
        $query->where('cohorte_id', $cohorte->id);
    }

    /**
     * Scope pour filtrer par mentor
     * 
     * CAS D'USAGE :
     * - Tableau de bord mentor
     * - Rapports de supervision
     * - Charge de travail mentor
     * - Performance par mentor
     * 
     * EXEMPLES :
     * // Marins d'un mentor
     * $supervises = FcmMarin::mentor($mentor)->get();
     * 
     * // Performance du mentor
     * $tauxMoyen = FcmMarin::mentor($mentor)
     *     ->with('lignes_dans_marin_parcours')
     *     ->get()
     *     ->avg(fn($fm) => $fm->getTauxGlobal());
     * 
     * // Marins en retard pour ce mentor
     * $enRetard = FcmMarin::mentor($mentor)
     *     ->whereHas('lignes_dans_marin_parcours', function($q) {
     *         $q->where('taux_global', '<', 50);
     *     })->get();
     */
    public function scopeMentor(Builder $query, Marin $mentor): void
    {
        $query->where('mentor_id', $mentor->id);
    }

    /**
     * Scope pour les marins sans mentor
     * 
     * CAS D'USAGE :
     * - Attribution de mentors
     * - Rapports de couverture
     * - Alertes de gestion
     * - Statistiques d'attribution
     * 
     * EXEMPLES :
     * // Liste pour attribution
     * $sansmentors = FcmMarin::sansMentor()->get();
     * 
     * // Alerte si trop nombreux
     * $nombreSansMentor = FcmMarin::sansMentor()->count();
     * if ($nombreSansMentor > 10) {
     *     // Envoyer alerte administrateur
     * }
     * 
     * // Attribution automatique
     * $mentorDisponible = Marin::mentor()
     *     ->get()
     *     ->sortBy(fn($m) => $m->nombreMarinsSupervises())
     *     ->first();
     * 
     * FcmMarin::sansMentor()->limit(5)->get()->each(function($fm) use ($mentorDisponible) {
     *     $fm->assignerMentor($mentorDisponible);
     * });
     */
    public function scopeSansMentor(Builder $query): void
    {
        $query->whereNull('mentor_id');
    }

    /**
     * Scope pour les marins sans cohorte
     * 
     * CAS D'USAGE :
     * - Attribution de cohortes
     * - Validation de données
     * - Rapports de complétude
     * - Nettoyage de données
     * 
     * EXEMPLES :
     * // Attribution de cohorte par défaut
     * $cohorteActive = Cohorte::where('active', true)->first();
     * FcmMarin::sansCohorte()->get()->each(function($fm) use ($cohorteActive) {
     *     $fm->assignerCohorte($cohorteActive);
     * });
     * 
     * // Rapport de complétude
     * $stats = [
     *     'total' => FcmMarin::count(),
     *     'sans_cohorte' => FcmMarin::sansCohorte()->count(),
     *     'sans_mentor' => FcmMarin::sansMentor()->count()
     * ];
     */
    public function scopeSansCohorte(Builder $query): void
    {
        $query->whereNull('cohorte_id');
    }

    // ========================================
    // MÉTHODES D'INFORMATION
    // ========================================

    /**
     * Retourne un résumé des informations FCM
     * 
     * CAS D'USAGE :
     * - API REST
     * - Exports de données
     * - Widgets dashboard
     * - Rapports synthétiques
     * 
     * EXEMPLES :
     * // API endpoint
     * public function show(FcmMarin $fcmMarin) {
     *     return response()->json($fcmMarin->getInfosFcm());
     * }
     * 
     * // Export Excel
     * foreach ($fcmMarins as $fm) {
     *     $data[] = $fm->getInfosFcm();
     * }
     * Excel::create('rapport_fcm', $data);
     * 
     * // Widget Filament
     * protected function getCards(): array {
     *     $fcmMarin = FcmMarin::find($this->marinId);
     *     $infos = $fcmMarin->getInfosFcm();
     *     
     *     return [
     *         Card::make('Progression', $infos['taux_global'] . '%'),
     *         Card::make('Mentor', $infos['mentor']),
     *         Card::make('Cohorte', $infos['cohorte'])
     *     ];
     * }
     */
    public function getInfosFcm(): array
    {
        return [
            'marin' => $this->marin?->fullNameAndGrade,
            'cohorte' => $this->cohorte?->libelle_court,
            'mentor' => $this->mentor?->fullNameAndGrade,
            'taux_global' => $this->getTauxGlobal(),
            'taux_stage' => $this->getTauxStage(),
            'taux_activite' => $this->getTauxActivite(),
            'has_parcours' => $this->hasParcours(),
            'has_mentor' => $this->hasMentor(),
            'has_cohorte' => $this->hasCohorte()
        ];
    }
}
