<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\ParcoursSerialise as  ParcoursSerialiseBase;

use Modules\FcmCommun\Models\User;

class ParcoursSerialise extends ParcoursSerialiseBase
{
    protected $prefix = 'fcmcentral_';

    public function users()
    {
        // return $this->hasManyThrough(
        //         related: User::class,
        //         through: UserParcours::class,
        //         firstKey: 'parcours_id',
        //         secondKey: 'uuid',
        //         localKey: 'id',
        //         secondLocalKey: 'user_id'
        //     );
        return $this->belongsToMany(
            related: User::class,
            table: 'fcmcentral_user_parcours',
            foreignPivotKey: 'parcours_id',
            relatedPivotKey: 'user_id'
        );
    }

    public function userparcours()
    {
        return $this->hasMany(UserParcours::class, 'parcours_id');
    }
}
