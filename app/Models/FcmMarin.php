<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Modules\FcmCentral\Database\Factories\FcmMarinFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\Cohorte;
use Modules\RH\Models\Marin as RHMarin;

class FcmMarin extends Model
{
    use HasTablePrefix;
    use HasFactory;

    protected $fillable = [
        'rh_marin_id',
        'data',
        'documents_annexes',
        'cohorte_id',
        'date_sedimentation',
        'date_fcm',
        'date_competence',
        'gel',
    ];

    protected $casts = [
        'data' => 'array',
        'documents_annexes' => 'array',
    ];

    // Relations
    public function marin()
    {
        return $this->belongsTo(Marin::class, 'rh_marin_id');
    }

    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(
            Marin::class,
            'fcmcentral_fcm_marin_mentors',
            'fcm_marin_id',
            'marin_id'
        )->withTimestamps();
    }

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

    public function lignesDansMarinParcours()
    {
        return $this->hasMany(MarinParcours::class, 'fcmmarin_id');
    }

    // Attributs calculés
    public function getDonneesDuParcoursAttribute()
    {
        // Tri explicite : sans ORDER BY, MySQL ne garantit pas l'ordre de retour.
        // S'il existe plusieurs lignes MarinParcours pour ce marin (réaffectation, historique),
        // on veut systématiquement la plus récente, de façon déterministe pour tous les appelants
        // (affichage ET mutation doivent toujours cibler la même ligne).
        return $this->lignesDansMarinParcours->sortByDesc('id')->first();
    }

    // Méthodes principales
    public function hasMentor(): bool
    {
        return $this->mentors()->exists();
    }

    public function hasCohorte(): bool
    {
        return !is_null($this->cohorte_id);
    }

    public function hasParcours(): bool
    {
        return null !== $this->donnees_du_parcours;
    }

    public function getTauxGlobal(): ?float
    {
        return $this->donnees_du_parcours?->taux_global;
    }

    public function getTauxStage(): ?float
    {
        return $this->donnees_du_parcours?->taux_stage;
    }

    public function getTauxActivite(): ?float
    {
        return $this->donnees_du_parcours?->taux_activite;
    }

    // Gestion cohorte/mentors
    public function assignerCohorte(Cohorte $cohorte): void
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
        $this->refresh();
        $this->load('cohorte');
    }

    public function assignerMentor(Marin|RHMarin $mentor): void
    {
        $this->mentors()->syncWithoutDetaching([$mentor->id]);
        $this->load('mentors');
    }

    public function retirerMentor(Marin|RHMarin $mentor): void
    {
        $this->mentors()->detach($mentor->id);
        $this->load('mentors');
    }

    public function retirerTousLesMentors(): void
    {
        $this->mentors()->detach();
        $this->load('mentors');
    }

    public function retirerCohorte(): void
    {
        $this->cohorte_id = null;
        $this->save();
        $this->refresh();
        $this->load('cohorte');
    }

    // Setters personnalisés
    public function setCohorteAttribute(Cohorte $cohorte)
    {
        $this->assignerCohorte($cohorte);
    }

    // Scopes
    public function scopeCohorte(Builder $query, Cohorte $cohorte): void
    {
        $query->where('cohorte_id', $cohorte->id);
    }

    public function scopeAvecMentor(Builder $query, RHMarin $mentor): void
    {
        $query->whereHas('mentors', fn ($q) => $q->where('marin_id', $mentor->id));
    }

    public function scopeSansMentor(Builder $query): void
    {
        $query->doesntHave('mentors');
    }

    public function scopeSansCohorte(Builder $query): void
    {
        $query->whereNull('cohorte_id');
    }

    // Infos synthétiques
    public function getInfosFcm(): array
    {
        return [
            'marin' => $this->marin?->fullNameAndGrade,
            'cohorte' => $this->cohorte?->libelle_court,
            'mentors' => $this->mentors->map(fn ($m) => $m->fullNameAndGrade)->join(', '),
            'taux_global' => $this->getTauxGlobal(),
            'taux_stage' => $this->getTauxStage(),
            'taux_activite' => $this->getTauxActivite(),
            'has_parcours' => $this->hasParcours(),
            'has_mentor' => $this->hasMentor(),
            'has_cohorte' => $this->hasCohorte(),
        ];
    }

    // Jalon FCM Marin
    public function getJalonFcmLibelle(): ?string
    {
        if ($this->date_sedimentation && !$this->date_fcm && !$this->date_competence) {
            return 'SED';
        }
        if ($this->date_sedimentation && $this->date_fcm && !$this->date_competence) {
            return 'FCM';
        }
        if ($this->date_sedimentation && $this->date_fcm && $this->date_competence) {
            return 'FIN';
        }

        return null;
    }

    // Factory
    protected static function newFactory()
    {
        return FcmMarinFactory::new();
    }
}
