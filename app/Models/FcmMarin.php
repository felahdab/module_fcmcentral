<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Database\Factories\FcmMarinFactory;

class FcmMarin extends Model
{
    use HasTablePrefix, HasFactory;

    
    // Relation entre FcmCentral FcmMarins et FcmParcoursSerialises
    public function parcoursSerialises(): BelongsToMany
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'fcmmarin_id', 'parcoursserialise_id')
            ->withPivot('id','taux_global', 'taux_stage', 'taux_activite','parcoursmarin')
            ->withTimestamps();
    }
   
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'rh_marin_id',
        'data',
        'cohorte_id',
        'mentor_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

     /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FcmMarinFactory::new();
    }

  
    public function marin()
    {
        return $this->belongsTo(Marin::class,'rh_marin_id');
    }

    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }

    public function mentor()
    {
       // return $this->belongsTo(Marin::class,'mentor_id');
       return $this->belongsTo(Marin::class,'mentor_id');
    }

    /**
     * La relation ci-dessous facilite l'accès aux lignes dans marin_parcours qui sont relative à cet objet.
     * Mais en pratique, il ne doit y avoir qu'une seule ligne comme cela.
     */
    public function lignes_dans_marin_parcours()
    {
        return $this->hasMany(MarinParcours::class, 'fcmmarin_id');
    }

    /**
     * Grace à la définition de cet attribut, le developpeur peut utiliser les méthodes suivantes:
     * $marin->complements_fcm->cohorte
     * $marin->complements_fcm->mentor
     * $marin->complements_fcm->donnees_du_parcours
     */
    public function getDonneesDuParcoursAttribute()
    {
        return $this->lignes_dans_marin_parcours->first();
    }

    public function setCohorteAttribute(Cohorte $cohorte)
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
    }

    public function setMentorAttribute(Marin $mentor)
    {
        $this->mentor_id = $mentor->id;
        $this->save();
    }



    // ========================================
    // MÉTHODES POUR LES TESTS
    // ========================================

    /**
     * Vérifie si le marin a un mentor assigné
     */
    public function hasMentor(): bool
    {
        return !is_null($this->mentor_id);
    }

    /**
     * Vérifie si le marin appartient à une cohorte
     */
    public function hasCohorte(): bool
    {
        return !is_null($this->cohorte_id);
    }



    /**
     * Vérifie si le marin a un parcours assigné
     */
    public function hasParcoursAssigne(): bool
    {
        return $this->parcoursSerialises()->count() > 0;
    }

    /**
     * Retourne tous les marins de la même cohorte (sauf lui-même)
     */
    public function marinsDeCohorte()
    {
        if (!$this->cohorte_id) {
            return collect();
        }
        
        return static::where('cohorte_id', $this->cohorte_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Assigne un parcours actif en désactivant les autres
     */
    public function assignerParcoursActif(int $parcoursId): void
    {
        // Détacher tous les parcours existants
        $this->parcoursSerialises()->detach();
        
        // Attacher le nouveau parcours
        $this->parcoursSerialises()->attach($parcoursId, [
            'parcoursmarin' => json_encode(['fonctions' => []]),
            'taux_global' => 0,
            'taux_stage' => 0,
            'taux_activite' => 0,
        ]);
    }

    /**
     * Vérifie si le marin respecte la contrainte d'un seul parcours
     */
    public function respecteContrainteParcours(): bool
    {
        return $this->parcoursSerialises()->count() <= 1;
    }

    /**
     * Met à jour les taux de progression d'un parcours
     */
    public function mettreAJourTauxParcours(int $parcoursId, array $taux): void
    {
        $this->parcoursSerialises()->updateExistingPivot($parcoursId, $taux);
    }

    /**
     * Met à jour les données JSON d'un parcours
     */
    public function mettreAJourDonneesParcours(int $parcoursId, array $donnees): void
    {
        $this->parcoursSerialises()->updateExistingPivot($parcoursId, [
            'parcoursmarin' => json_encode($donnees),
            'updated_at' => now(),
        ]);
    }

    /**
     * Vérifie si le marin peut mettre à jour son parcours
     */
    public function peutMettreAJourParcours(int $parcoursId): bool
    {
        $pivot = $this->parcoursSerialises()
            ->where('parcoursserialise_id', $parcoursId)
            ->first()?->pivot;
            
        return $pivot?->statut !== 'termine';
    }

    /**
     * Vérifie si le profil FCM est complètement paramétré
     */
    public function estCompletementParametre(): bool
    {
        return $this->hasMentor() && 
               $this->hasCohorte() && 
               $this->hasParcoursAssigne();
    }

    /**
     * Obtient la progression d'un parcours spécifique
     */
    public function getParcoursProgress(int $parcoursId): array
    {
        $pivot = $this->parcoursSerialises()
            ->where('parcoursserialise_id', $parcoursId)
            ->first()?->pivot;
            
        return [
            'taux_global' => $pivot?->taux_global ?? 0,
            'taux_stage' => $pivot?->taux_stage ?? 0,
            'taux_activite' => $pivot?->taux_activite ?? 0,
        ];
    }

    /**
     * Obtient les données JSON d'un parcours
     */
    public function getDonneesParcoursJson(int $parcoursId): ?array
    {
        $pivot = $this->parcoursSerialises()
            ->where('parcoursserialise_id', $parcoursId)
            ->first()?->pivot;
            
        return $pivot ? json_decode($pivot->parcoursmarin, true) : null;
    }

    /**
     * Change le mentor (avec sauvegarde automatique)
     */
    public function changerMentor(\Modules\RH\Models\Marin $mentor): void
    {
        $this->mentor_id = $mentor->id;
        $this->save();
        // Rafraichir les relations
        $this->refresh();
        $this->load('mentor');
    }

    /**
     * Change la cohorte (avec sauvegarde automatique)
     */
    public function changerCohorte(Cohorte $cohorte): void
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
    }

    /**
     * Retire le mentor
     */
    public function retirerMentor(): void
    {
        $this->mentor_id = null;
        $this->save();
         // Rafraichir les relations
         $this->refresh();
         $this->load('mentor');
    }

    /**
     * Retire de la cohorte
     */
    public function retirerDeCohorte(): void
    {
        $this->cohorte_id = null;
        $this->save();
    }

    /**
     * Obtient le statut général du marin FCM
     */
    public function getStatutFcm(): string
    {
        $data = $this->data ?? [];
        return $data['statut_fcm'] ?? 'inconnu';
    }

    /**
     * Met à jour le statut FCM
     */
    public function mettreAJourStatutFcm(string $statut): void
    {
        $data = $this->data ?? [];
        $data['statut_fcm'] = $statut;
        $data['derniere_modification'] = now()->toISOString();
        
        $this->data = $data;
        $this->save();
    }

    /**
     * Vérifie si le marin est actif en FCM
     */
    public function estActifEnFcm(): bool
    {
        return $this->getStatutFcm() === 'actif';
    }

   // ========================================
    // SCOPES MENTOR
    // ========================================

    /**
     * Scope pour les marins qui ont un mentor
     */
    public function scopeAvecMentor(Builder $query): Builder
    {
        return $query->whereNotNull('mentor_id');
    }

    /**
     * Scope pour les marins sans mentor
     */
    public function scopeSansMentor(Builder $query): Builder
    {
        return $query->whereNull('mentor_id');
    }

    /**
     * Scope pour les marins avec un mentor spécifique
     */
    public function scopeAvecMentorSpecifique(Builder $query, int $mentorId): Builder
    {
        return $query->where('mentor_id', $mentorId);
    }

    // ========================================
    // SCOPES COHORTE
    // ========================================

    /**
     * Scope pour les marins qui ont une cohorte
     */
    public function scopeAvecCohorte(Builder $query): Builder
    {
        return $query->whereNotNull('cohorte_id');
    }

    /**
     * Scope pour les marins sans cohorte
     */
    public function scopeSansCohorte(Builder $query): Builder
    {
        return $query->whereNull('cohorte_id');
    }

    /**
     * Scope pour les marins avec une cohorte spécifique
     */
    public function scopeAvecCohorteSpecifique(Builder $query, int $cohorteId): Builder
    {
        return $query->where('cohorte_id', $cohorteId);
    }

    // ========================================
    // SCOPES COMBINÉS
    // ========================================

    /**
     * Scope pour les marins sans mentor ni cohorte
     */
    public function scopeSansMentorNiCohorte(Builder $query): Builder
    {
        return $query->whereNull('mentor_id')
                    ->whereNull('cohorte_id');
    }

    /**
     * Scope pour les marins avec mentor ET cohorte
     */
    public function scopeAvecMentorEtCohorte(Builder $query): Builder
    {
        return $query->whereNotNull('mentor_id')
                    ->whereNotNull('cohorte_id');
    }

    /**
     * Scope pour les marins avec mentor MAIS sans cohorte
     */
    public function scopeAvecMentorSansCohorte(Builder $query): Builder
    {
        return $query->whereNotNull('mentor_id')
                    ->whereNull('cohorte_id');
    }

    /**
     * Scope pour les marins avec cohorte MAIS sans mentor
     */
    public function scopeAvecCohorteSansMentor(Builder $query): Builder
    {
        return $query->whereNotNull('cohorte_id')
                    ->whereNull('mentor_id');
    }

    // ========================================
    // SCOPES STATUT FCM
    // ========================================

    /**
     * Scope pour les marins actifs en FCM
     */
    public function scopeActifsEnFcm(Builder $query): Builder
    {
        return $query->where('statut_fcm', 'actif');
    }

    /**
     * Scope pour les marins inactifs en FCM
     */
    public function scopeInactifsEnFcm(Builder $query): Builder
    {
        return $query->where('statut_fcm', 'inactif');
    }

    /**
     * Scope pour les marins en cours de FCM
     */
    public function scopeEnCoursEnFcm(Builder $query): Builder
    {
        return $query->where('statut_fcm', 'en_cours');
    }




    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Obtient un résumé des statistiques du marin
     */
    public function getStatistiques(): array
    {
        $parcours = $this->parcoursSerialises->first();
        
        return [
            'a_mentor' => $this->hasMentor(),
            'a_cohorte' => $this->hasCohorte(),
            'a_parcours' => $this->hasParcoursAssigne(),
            'statut_fcm' => $this->getStatutFcm(),
            'taux_global' => $parcours?->pivot?->taux_global ?? 0,
            'taux_stage' => $parcours?->pivot?->taux_stage ?? 0,
            'taux_activite' => $parcours?->pivot?->taux_activite ?? 0,
            'est_complet' => $this->estCompletementParametre(),
        ];
    }

    /**
     * Obtient les informations de contact du mentor
     */
    public function getInfosMentor(): ?array
    {
        if (!$this->mentor) {
            return null;
        }

        return [
            'id' => $this->mentor->id,
            'nom' => $this->mentor->nom,
            'email' => $this->mentor->email ?? null,
            'grade' => $this->mentor->grade ?? null,
        ];
    }

    /**
     * Obtient les informations de la cohorte
     */
    public function getInfosCohorte(): ?array
    {
        if (!$this->cohorte) {
            return null;
        }

        return [
            'id' => $this->cohorte->id,
            'nom' => $this->cohorte->nom,
            'effectif' => $this->cohorte->fcmMarins()->count(),
            'statut' => $this->cohorte->statut ?? null,
        ];
    }


}