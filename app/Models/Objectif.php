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
        'competence_savoirfaire_id',
        'uuid',
        'code',
        'libelle_long',
        'libelle_court',
        'coeff',
        'ordre',
    ];

    public function competenceSavoirfaire()
    {
        return $this->belongsTo(CompetenceSavoirfaire::class, 'competence_savoirfaire_id');
    }

    
    protected static function newFactory(): ObjectifFactory
    {
        return ObjectifFactory::new();
    }

    


}
