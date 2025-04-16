<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\ParcoursSerialise as  ParcoursSerialiseBase;

use Modules\FcmCommun\Models\Marin;
use Modules\FcmCommun\Models\MarinParcours;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParcoursSerialise extends ParcoursSerialiseBase
{
    use HasTablePrefix;

    // A supp debut
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
            table: 'fcmcentral_marin_parcours',
            foreignPivotKey: 'parcours_id',
            relatedPivotKey: $this->prefix.'marin_id'
        );
    }

    public function marinparcours()
    {
        return $this->hasMany(MarinParcours::class, 'parcours_id');
    }
    // A Supp Fin


    // Relation entre FcmCentral  ParcoursSerialises et FcmMarins
    public function fcmMarins(): BelongsToMany
    {
        return $this->belongsToMany(FcmMarin::class, 'fcmcentral_marin_parcours', 'parcoursserialise_id', 'fcmmarin_id')
        ->withPivot('taux_global', 'taux_stage', 'taux_activite','parcoursmarin')
        ->withTimestamps();
    }

    public function parcours()
    {
        return $this->belongsTo(Parcours::class, "parcours_id", "id");
    }



}
