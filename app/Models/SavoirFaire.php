<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\SavoirFaireFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class SavoirFaire extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["libelle_long","libelle_court"];
    
    protected static function newFactory(): SavoirFaireFactory
    {
        return SavoirFaireFactory::new();
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class, 'fcmcentral_competence_savoirfaire', 'savoirfaire_id', 'competence_id')
        ->withTimestamps();
    }

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'fcmcentral_savoirfaire_stage', 'savoirfaire_id', 'stage_id')
        ->withTimestamps();
    }

    public function objectifs(): BelongsToMany
    {
        return $this->belongsToMany(Objectif::class, 'fcmcentral_savoirfaire_objectif', 'savoirfaire_id', 'objectif_id')
        ->withTimestamps();
    }
}
