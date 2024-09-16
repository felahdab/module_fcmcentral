<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\Marin as BaseMarin;

use Modules\FcmCentral\Models\ParcoursSerialise;

class Marin extends BaseMarin
{
    public function parcours_attribues()
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_user_parcours', 'user_id', 'parcours_id');
    }
    

}