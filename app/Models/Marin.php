<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\Marin as BaseMarin;
use Modules\RH\Models\Marin as RhMarin;
use Modules\FcmCommun\Models\Cohorte;


use Modules\FcmCentral\Models\ParcoursSerialise;

class Marin extends BaseMarin
{

    protected $prefix = 'fcmcentral_'; 
    protected $table = 'fcmcentral_marins';


    public function parcours_attribues()
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'marin_id', 'parcours_id');
    }
    
    public function marin()
    {
        return $this->belongsTo(RhMarin::class,'rh_marin_id');
    }

    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }


    public function marinParcours()
    {
        return $this->hasMany(MarinParcours::class);
    }

    public function mentor()
    {
        return $this->belongsTo(RhMarin::class,'mentor_id');
    }


}