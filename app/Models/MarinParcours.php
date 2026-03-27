<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Database\Factories\MarinParcoursFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\MarinParcours as BaseModel;

class MarinParcours extends BaseModel
{
    use HasFactory;
    use HasTablePrefix;

    /*
        public function parcoursSerialise()
        {
            return $this->belongsTo(ParcoursSerialise::class, 'parcours_id');
        }

     */
    public function ligne_dans_fcm_marins()
    {
        return $this->belongsTo(FcmMarin::class, 'fcmmarin_id');
    }

    public function parcours_serialise()
    {
        return $this->belongsTo(ParcoursSerialise::class, 'parcoursserialise_id');
    }

    public function fcmMarin()
    {
        return $this->belongsTo(FcmMarin::class, 'fcmmarin_id');
    }

    public function marin()
    {
        return $this->hasOneThrough(
            Marin::class,
            FcmMarin::class,
            'id',                   // Clef locale FcmMarin
            'id',                   // Clef locale Marin
            'fcmmarin_id',          // Clef locale MarinPArcours
            'rh_marin_id',          // Clef locale FcmMarin
        );
    }

    protected static function newFactory(): MarinParcoursFactory
    {
        return MarinParcoursFactory::new();
    }
}
