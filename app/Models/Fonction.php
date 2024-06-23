<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\FonctionFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Fonction extends Model
{
    use HasFactory;
    use HasTablePrefix;
    use HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["libelle_long","libelle_court"];
    
    protected static function newFactory(): FonctionFactory
    {
        return FonctionFactory::new();
    }

    public function parcours(): BelongsToMany
    {
        return $this->belongsToMany(Parcours::class, 'fcmcentral_fonction_parcours', 'fonction_id', 'parcours_id')
        ->withTimestamps();
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class, 'fcmcentral_competence_fonction', 'fonction_id', 'competence_id')
        ->withTimestamps();
    }


}
