<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\SavoirfaireFactory;
use Modules\FcmCentral\Models\ActiviteSavoirfairePivot;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Savoirfaire extends Model
{
    use HasFactory;
    use HasTablePrefix;



    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "uuid",
        "code",
        "libelle_long",
        "libelle_court",
        "code_sicomp",
        "same",
        "ordre",
        "competence_id",
        "domaine_id",
        "color",
    ];



    protected static function newFactory(): SavoirfaireFactory
    {
        return Savoirfairefactory::new();
    }

    public function competence()
    {
        return $this->belongsTo(Competence::class, 'competence_id');
    }

    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'domaine_id');
    }

    // Relation many-to-many avec Competence via le pivot enrichi
    public function competences()
    {
        return $this->belongsToMany(
            Competence::class,
            'fcmcentral_competence_savoirfaires',
            'savoirfaire_id',
            'competence_id'
        )
        ->withPivot([
            'id',
            'uuid',
            'ordre',
            'heures',
            'jours',
            'libelle_court',
            'savoirfaire_type_id'
        ])
        ->withTimestamps();
    }



    // Accès direct aux objets pivot
    public function competenceSavoirfaires()
    {
        return $this->hasMany(CompetenceSavoirfaire::class, 'savoirfaire_id');
    }

    public function objectifs()
{
    return $this->hasManyThrough(
        Objectif::class,
        CompetenceSavoirfaire::class,
        'savoirfaire_id', // Clé étrangère dans annee_savoirfaires
        'competence_savoirfaire_id', // Clé étrangère dans objectifs
        'id', // Clé locale dans savoirfaires
        'id' // Clé locale dans annee_savoirfaires
    );
}




    // public function competences(): BelongsToMany
    // {
    //     return $this->belongsToMany(Competence::class, 'fcmcentral_competence_savoirfaire', 'savoirfaire_id', 'competence_id')
    //         ->withPivot('coeff', 'duree', 'ordre', 'an_acquis', 'mod_acquis')
    //         ->withTimestamps();
    // }

    // public function activites(): BelongsToMany
    // {
    //     return $this->belongsToMany(Activite::class, 'fcmcentral_activite_savoirfaire', 'savoirfaire_id',  'activite_id')
    //             ->withPivot('coeff', 'duree', 'ordre','data')
    //             ->withTimestamps()
    //             ->using(ActiviteSavoirfairePivot::class);
    // }

    // public function domaine()
    // {
    //     return $this->belongsTo(Domaine::class, "domaine_id", "id");
    // }

    // public function competencePrincipale()
    // {
    // return $this->belongsTo(Competence::class, 'competence_id');
    // }

    // public function activitesPrincipaux()
    // {
    //     return $this->hasMany(Activite::class, 'savoirfaire_id');
    // }

}
