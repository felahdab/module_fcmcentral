<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Modules\FcmCentral\Database\Factories\CompetenceFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Competence extends Model
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
        "parcours_type_id",
        "ordre",
        "color",
    ];
    
    protected static function newFactory(): CompetenceFactory
    {
        return CompetenceFactory::new();
    }

    // Catalogue
    public function parcoursType()
    {
        return $this->belongsTo(ParcoursType::class, 'parcours_type_id');
    }

    public function savoirfaires()
    {
        return $this->hasMany(Savoirfaire::class, 'competence_id');
    }

    // Parcours
    public function affectation()
    {
        return $this->belongsTo(Affectation::class, 'affectation_id');
    }

   


      // Relation Many To Many avec Savoirfaire via pivot
      public function savoirfairesPivot()
      {
          return $this->belongsToMany(
              Savoirfaire::class,
              'fcmcentral_competence_savoirfaires',
              'competence_id',
              'savoirfaire_id'
          )
          ->withPivot([
              'id', // clé primaire du pivot
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
      public function competenceSavoirfaires(){
          return $this->hasMany(CompetenceSavoirfaire::class,'competence_id');
      }




    // public function fonctions(): BelongsToMany
    // {
    //     return $this->belongsToMany(Fonction::class, 'fcmcentral_competence_fonction', 'competence_id', 'fonction_id')
    //         ->withPivot('ordre', 'same')
    //         ->withTimestamps();
    // }

    // public function savoirfaires(): BelongsToMany
    // {
    //     return $this->belongsToMany(Savoirfaire::class, 'fcmcentral_competence_savoirfaire', 'competence_id', 'savoirfaire_id')
    //         ->withPivot('coeff', 'duree', 'ordre', 'an_acquis', 'mod_acquis')
    //         ->withTimestamps();
    // }

    // public function savoirfairesPrincipaux()
    // {
    //     return $this->hasMany(Savoirfaire::class, 'competence_id');
    // }

}
