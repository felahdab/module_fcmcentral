<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\FcmCentral\Database\Factories\AffectationFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;

class Affectation extends Model
{
    use HasFactory;
    use HasTablePrefix;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'code',
        'libelle_long',
        'libelle_court',
        'ordre',
        'color',
    ];

    // //////////////////
    // / Parcours      //
    // //////////////////

    // Relation avec Parcours (ManyToMAny)
    public function parcours(): BelongsToMany
    {
        return $this->belongsToMany(
            Parcours::class,
            'fcmcentral_affectation_parcours',
            'affectation_id',
            'parcours_id'
        )
            ->withPivot('ordre')
            ->withTimestamps()
        ;
    }

    // Relation avec Competences
    public function competences()
    {
        return $this->hasMany(Competence::class, 'affectation_id');
    }

    // acces direct aux CompetenceSavoirfaires depuis les competences de l'affectations (RelationManager)
    public function competenceSavoirfaires()
    {
        return $this->hasManyThrough(
            CompetenceSavoirfaire::class,
            Competence::class,
            'affectation_id',
            'competence_id',
            'id',
            'id'
        );
    }

    public function objectifs()
    {
        return $this->hasManyThrough(
            Objectif::class,
            CompetenceSavoirfaire::class,
            'competence_id',
            'competence_savoirfaire_id',
            'id',
            'id'
        )->whereRaw('1=0'); // ✅ toujours vide — juste pour satisfaire Filament
    }

    protected static function newFactory(): AffectationFactory
    {
        return AffectationFactory::new();
    }

    // public function competences(): BelongsToMany
    // {
    //     return $this->belongsToMany(Competence::class, 'fcmcentral_competence_annee', 'annee_id', 'competence_id')
    //     ->withPivot('ordre', 'same')
    //     ->withTimestamps();
    // }
}
