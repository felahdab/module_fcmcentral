<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


use Modules\FcmCentral\Database\Factories\ObjectifFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Objectif extends Model
{
    use HasFactory;
    use HasTablePrefix;

    


    protected $fillable = [
        'annee_savoirfaire_id',
        'libelle_long',
        'libelle_court',
        'coeff',
        'ordre',
    ];

    public function anneeSavoirfaire()
    {
        return $this->belongsTo(AnneeSavoirfaire::class, 'annee_savoirfaire_id');
    }

    
    protected static function newFactory(): ObjectifFactory
    {
        return ObjectifFactory::new();
    }

    


}
