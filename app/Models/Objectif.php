<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\ObjectifFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Objectif extends Model
{
    use HasFactory;
    use HasTablePrefix;

    // ✅ Constantes pour les types d'activité
    const TYPE_TACHE = 'TACHE';
    const TYPE_STAGE = 'STAGE';
  

  


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "libelle_long",
                            "libelle_court", 
                            "url", 
                            "duree",
                            "coeff",
                            "duree_validite", 
                            "prerequis", 
                            "type_activite"
                        ];
                            
    protected $casts =[
                            'data' => 'array',
                            'duree_validite' => 'integer',
                            'coeff' => 'decimal:2',

                            ];
    
    protected static function newFactory(): ObjectifFactory
    {
        return ObjectifFactory::new();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(Savoirfaire::class, 'fcmcentral_objectif_savoirfaire', 'objectif_id', 'savoirfaire_id')
        ->withPivot('coeff', 'duree', 'ordre','data')
        ->withTimestamps();
    }

    public function savoirfairePrincipale()
    {
    return $this->belongsTo(Savoirfaire::class, 'savoirfaire_id');
    }

    /**
     *  Obtenir les types d'Activités
     */
    public static function getTypesObjectif(): array
    {
        return [
            self::TYPE_STAGE => 'Stage',
            self::TYPE_OBJECTIF => 'Objectif',
            self::TYPE_EAD => 'EAD',
        ];
    }

    /**
     *  Validation des types d'activité
     */
    public static function getValidTypes(): array
    {
        return [self::TYPE_STAGE, self::TYPE_OBJECTIF, self::TYPE_EAD];
    }


    public function isStage(): bool
    {
        return $this->type_activite === self::TYPE_STAGE;
    }

    public function isObjectif(): bool
    {
        return $this->type_activite === self::TYPE_OBJECTIF;
    }

    public function isEad(): bool
    {
        return $this->type_activite === self::TYPE_EAD;
    }

    /**
     * SCope
     */
    public function scopeStages($query)
    {
        return $query->where('type_activite', self::TYPE_STAGE);
    }

    public function scopeObjectifs($query) 
    {
        return $query->where('type_activite', self::TYPE_OBJECTIF);
    }


    public function scopeEads($query) 
    {
        return $query->where('type_activite', self::TYPE_EAD);
    }



}
