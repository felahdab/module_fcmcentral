<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\StageFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Stage extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["libelle_long","libelle_court"];
    
    protected static function newFactory(): StageFactory
    {
        return StageFactory::new();
    }

    public function savoirfaires(): BelongsToMany
    {
        return $this->belongsToMany(SavoirFaire::class, 'fcmcentral_savoirfaire_stage', 'stage_id', 'savoirfaire_id')
        ->withTimestamps();
    }
}
