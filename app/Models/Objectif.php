<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\ObjectifFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Objectif extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["libelle_long","libelle_court"];
    
    protected static function newFactory(): ObjectifFactory
    {
        return ObjectifFactory::new();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(SavoirFaire::class, 'fcmcentral_savoirfaire_objectif', 'objectif_id', 'savoirfaire_id')
        ->withTimestamps();
    }
}
