<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Modules\FcmCentral\Database\Factories\CompetenceFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Competence extends Model
{
    use HasFactory;
    use HasTablePrefix;



    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "uuid",
        "libelle_long",
        "libelle_court",
        "url",
        "same",
    ];
    
    protected static function newFactory(): CompetenceFactory
    {
        return CompetenceFactory::new();
    }

    public function fonctions(): BelongsToMany
    {
        return $this->belongsToMany(Fonction::class, 'fcmcentral_competence_fonction', 'competence_id', 'fonction_id')
            ->withTimestamps();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(Savoirfaire::class, 'fcmcentral_competence_savoirfaire', 'competence_id', 'savoirfaire_id')
            ->withTimestamps();
    }
}
