<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\ActiviteFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Activite extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "libelle_long",
                            "libelle_court", 
                            "url", 
                            "duree_validite", 
                            "prerequis", 
                            "type"];
    
    protected static function newFactory(): ActiviteFactory
    {
        return ActiviteFactory::new();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(SavoirFaire::class, 'fcmcentral_savoirfaire_objectif', 'objectif_id', 'savoirfaire_id')
        ->withPivot('coefficient', 'duree', 'ordre')
        ->withTimestamps();
    }
}
