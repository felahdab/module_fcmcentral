<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\MarinParcours as BaseModel;
use Modules\FcmCentral\Traits\HasTablePrefix;



class MarinParcours extends BaseModel
{
    use HasTablePrefix;
    
/*
    public function parcoursSerialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }

    
    public function fcmMarin()
    {
        return $this->belongsTo(FcmMarin::class, 'marin_id');
    }
*/

   
}
