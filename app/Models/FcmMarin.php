<?php
namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCentral\Database\Factories\FcmMarinFactory;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCommun\Models\Cohorte;


class FcmMarin extends Model
{
    use HasTablePrefix, HasFactory;

    protected $fillable = [
        'rh_marin_id',
        'data',
        'cohorte_id',
        'mentor_id',
    ];

    protected $casts = [
        'data' => 'array',
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

    public function mentor()
    {
        return $this->belongsTo(Marin::class, 'mentor_id');
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
        return $this->lignesDansMarinParcours->first();
    }

    // Méthodes principales
    public function hasMentor(): bool
    {
        return !is_null($this->mentor_id);
    }

    public function hasCohorte(): bool
    {
        return !is_null($this->cohorte_id);
    }

    public function hasParcours(): bool
    {
        return $this->donnees_du_parcours !== null;
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

    // Gestion cohorte/mentor
    public function assignerCohorte(Cohorte $cohorte): void
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
        $this->refresh();
        $this->load('cohorte');
    }

    public function assignerMentor(Marin $mentor): void
    {
        $this->mentor_id = $mentor->id;
        $this->save();
        $this->refresh();
        $this->load('mentor');
    }

    public function retirerMentor(): void
    {
        $this->mentor_id = null;
        $this->save();
        $this->refresh();
        $this->load('mentor');
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

    public function setMentorAttribute(Marin $mentor)
    {
        $this->assignerMentor($mentor);
    }

    // Factory
    protected static function newFactory()
    {
        return FcmMarinFactory::new();
    }

    // Scopes
    public function scopeCohorte(Builder $query, Cohorte $cohorte): void
    {
        $query->where('cohorte_id', $cohorte->id);
    }

    public function scopeMentor(Builder $query, Marin $mentor): void
    {
        $query->where('mentor_id', $mentor->id);
    }

    public function scopeSansMentor(Builder $query): void
    {
        $query->whereNull('mentor_id');
    }

    public function scopeSansCohorte(Builder $query): void
    {
        $query->whereNull('cohorte_id');
    }

    // Infos synthétiques
    public function getInfosFcm(): array
    {
        return [
            'marin'        => $this->marin?->fullNameAndGrade,
            'cohorte'      => $this->cohorte?->libelle_court,
            'mentor'       => $this->mentor?->fullNameAndGrade,
            'taux_global'  => $this->getTauxGlobal(),
            'taux_stage'   => $this->getTauxStage(),
            'taux_activite'=> $this->getTauxActivite(),
            'has_parcours' => $this->hasParcours(),
            'has_mentor'   => $this->hasMentor(),
            'has_cohorte'  => $this->hasCohorte(),
        ];
    }
}
