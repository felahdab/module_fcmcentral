<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\UserParcours as BaseModel;
use Modules\RH\Models\Personne as User;

class UserParcours extends BaseModel
{
    protected $prefix = 'fcmcentral_';    

    public function parcoursserialise() 
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
