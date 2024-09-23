<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\MarinParcours as BaseModel;
use Modules\FcmCommun\Models\Marin;

class MarinParcours extends BaseModel
{
    protected $prefix = 'fcmcentral_';    

    public function parcoursserialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }

    public function marin()
    {
        return $this->belongsTo(Marin::class, "marin_id");
    }
}
