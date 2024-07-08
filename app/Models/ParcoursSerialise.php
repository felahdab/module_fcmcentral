<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Modules\FcmCentral\Traits\HasTablePrefix;


class ParcoursSerialise extends Model
{
    use HasTablePrefix;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "uuid",
                            "libelle_long",
                            "libelle_court", 
                            "version", 
                            "date_debut", 
                            "date_fin", 
                            "parcours"];

    protected $casts = [
        'parcours' => 'array',
    ];
    
}
