<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\ActiviteFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Activite extends Model
{
    use HasFactory;
    use HasTablePrefix;
  


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
                            "type_activite"];
                            
    protected $casts =[
                                'data' => 'array',
                            ];
    
    protected static function newFactory(): ActiviteFactory
    {
        return ActiviteFactory::new();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(Savoirfaire::class, 'fcmcentral_activite_savoirfaire', 'activite_id', 'savoirfaire_id')
        ->withPivot('coeff', 'duree', 'ordre','data')
        ->withTimestamps();
    }
}
