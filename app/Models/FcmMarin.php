<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\FcmMarin as BaseFcmMarin;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;




use Modules\FcmCentral\Models\ParcoursSerialise;


class FcmMarin extends BaseFcmMarin
{
    use HasTablePrefix;
   // Ancien voir avec Commandant
    // public function parcours_attribues()
    // {
    //     return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'marin_id', 'parcours_id');
    // }
    
    // Relation entre FcmCentral FcmMarins et FcmParcoursSerialises
    public function parcoursSerialises(): BelongsToMany
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'fcmmarin_id', 'parcoursserialise_id')
        ->withPivot('taux_global', 'taux_stage', 'taux_activite','parcoursmarin')
        ->withTimestamps();
    }
   
    

}
