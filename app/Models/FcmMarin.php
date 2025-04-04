<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\FcmMarin as BaseFcmMarin;




use Modules\FcmCentral\Models\ParcoursSerialise;


class FcmMarin extends BaseFcmMarin
{
    use HasTablePrefix;
   
    public function parcours_attribues()
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'marin_id', 'parcours_id');
    }
    
   
    

}
