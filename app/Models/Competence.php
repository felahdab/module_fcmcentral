<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Modules\FcmCentral\Database\Factories\CompetenceFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Competence extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["libelle_long","libelle_court"];
    
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
        return $this->belongsToMany(SavoirFaire::class, 'fcmcentral_competence_savoirfaire', 'competence_id', 'savoirfaire_id')
        ->withTimestamps();
    }
}
