<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\UserParcours as BaseModel;

class UserParcours extends BaseModel
{
    protected $prefix = 'fcmcentral_';    

    public function parcoursserialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }
}
