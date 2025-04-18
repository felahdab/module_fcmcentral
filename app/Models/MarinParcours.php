<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\MarinParcours as BaseModel;
use Modules\FcmCentral\Traits\HasTablePrefix;

use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\ParcoursSerialise;

class MarinParcours extends BaseModel
{
    use HasTablePrefix;
    
/*
    public function parcoursSerialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }

 */   
    public function ligne_dans_fcm_marins()
    {
        return $this->belongsTo(FcmMarin::class, 'fcmmarin_id');
    }

    public function parcours_serialise()
    {
        return $this->belongsTo(ParcoursSerialise::class, "parcoursserialise_id");
    }

   
}
