<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\AnneeFactory;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Annee extends Model
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
    ];
    
    protected static function newFactory(): AnneeFactory
    {
        return AnneeFactory::new();
    }

    public function parcours(): BelongsToMany
    {
        return $this->belongsToMany(Parcours::class, 'fcmcentral_annee_parcours', 'annee_id', 'parcours_id')
        ->withPivot('ordre')
        ->withTimestamps();
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class, 'fcmcentral_competence_annee', 'annee_id', 'competence_id')
        ->withPivot('ordre', 'same')
        ->withTimestamps();
    }


}
