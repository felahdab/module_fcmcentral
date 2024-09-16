<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\ParcoursSerialise as  ParcoursSerialiseBase;

use Modules\FcmCommun\Models\Marin;
use Modules\FcmCommun\Models\MarinParcours;

class ParcoursSerialise extends ParcoursSerialiseBase
{
    protected $prefix = 'fcmcentral_';

    public function marins()
    {
        // return $this->hasManyThrough(
        //         related: User::class,
        //         through: MarinParcours::class,
        //         firstKey: 'parcours_id',
        //         secondKey: 'uuid',
        //         localKey: 'id',
        //         secondLocalKey: 'user_id'
        //     );
        return $this->belongsToMany(
            related: Marin::class,
            table: 'fcmcentral_user_parcours',
            foreignPivotKey: 'parcours_id',
            relatedPivotKey: 'user_id'
        );
    }

    public function marinparcours()
    {
        return $this->hasMany(MarinParcours::class, 'parcours_id');
    }
}
