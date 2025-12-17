<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Database\Factories\CompetenceSavoirfaireFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;



class CompetenceSavoirfaire extends Model
{
    
    use HasFactory; 
    use HasTablePrefix;

    protected $fillable = [
        'id',
        'uuid',
        'competence_id',
        'savoirfaire_id',
        'savoirfaire_type_id',
        'heures',
        'jours',
        'libelle_court',
        'ordre',
    ];

    protected static function newFactory(): competenceSavoirFaireFactory
    {
        return CompetenceSavoirfaireFactory::new();
    }

    public function competence()
    {
        return $this->belongsTo(Competence::class, 'competence_id');
    }

    public function savoirfaire()
    {
        return $this->belongsTo(Savoirfaire::class, 'savoirfaire_id');
    }

    public function savoirfaireType()
    {
        return $this->belongsTo(SavoirfaireType::class, 'savoirfaire_type_id');
    }

    public function objectifs()
    {
        return $this->hasMany(Objectif::class, 'competence_savoirfaire_id');
    }
}
