<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\Cohorte as BaseModel;
use Modules\FcmCentral\Models\FcmMarin;

class Cohorte extends BaseModel
{
    public function fcmCentralMarins()
    {
        return $this->hasMany(FcmMarin::class, 'cohorte_id','id');
    }
}