<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;



use Modules\FcmCentral\Traits\HasTablePrefix;



class AnneeSavoirfaire extends Model
{
    
    use HasTablePrefix;

    protected $fillable = [
        'id',
        'uuid',
        'annee_id',
        'savoirfaire_id',
        'savoirfaire_type_id',
        'heures',
        'jours',
        'libelle_court',
        'ordre',
    ];

    public function annee()
    {
        return $this->belongsTo(Annee::class, 'annee_id');
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
        return $this->hasMany(Objectif::class, 'annee_savoirfaire_id');
    }
}
