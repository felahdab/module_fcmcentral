<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\ParcoursSerialise as  ParcoursSerialiseBase;

use App\Models\User;

class ParcoursSerialise extends ParcoursSerialiseBase
{
    protected $prefix = 'fcmcentral_';

    public function users()
    {
        return $this->hasManyThrough(
                User::class,
                UserParcours::class,
                'user_id',
                'uuid',
                'parcours_id',
                'id'
            );
    }

    public function userparcours()
    {
        return $this->hasMany(UserParcours::class, 'parcours_id', 'id');
    }
}
