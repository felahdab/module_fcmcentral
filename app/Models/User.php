<?php

namespace Modules\FcmCentral\Models;

use Modules\RH\Entities\Personne as BaseUser;

use Modules\FcmCentral\Models\ParcoursSerialise;

class User extends BaseUser
{
    public function parcours_attribues()
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_user_parcours', 'user_id', 'parcours_id');
    }
    

}