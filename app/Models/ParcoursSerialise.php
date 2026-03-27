<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\FcmCentral\Database\Factories\ParcoursSerialiseFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\Marin;
use Modules\FcmCommun\Models\MarinParcours;
use Modules\FcmCommun\Models\ParcoursSerialise as ParcoursSerialiseBase;

class ParcoursSerialise extends ParcoursSerialiseBase
{
    use HasFactory;
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

    // Relation entre FcmCentral  ParcoursSerialises et FcmMarins
    public function fcmMarins(): BelongsToMany
    {
        return $this->belongsToMany(FcmMarin::class, 'fcmcentral_marin_parcours', 'parcoursserialise_id', 'fcmmarin_id')
            ->withPivot('taux_global', 'taux_stage', 'taux_activite', 'parcoursmarin')
            ->withTimestamps()
        ;
    }

    // nom different car ca cree un bug
    public function parcoursModel()
    {
        return $this->belongsTo(Parcours::class, 'parcours_id', 'id');
    }
    // A Supp Fin

    protected static function newFactory(): ParcoursSerialiseFactory
    {
        return ParcoursSerialiseFactory::new();
    }
}
