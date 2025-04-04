<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Modules\FcmCentral\Database\Factories\ParcoursFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;

class Parcours extends Model
{
    use HasFactory;

    use HasTablePrefix;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'libelle_long',
        'libelle_cours',
        // 'version',
        // 'parcours',
        // 'date_debut',
    ];

    // protected $casts = [
    //     'parcours' => 'array',
    // ];
    
    protected static function newFactory(): ParcoursFactory
    {
        return ParcoursFactory::new();
    }

    protected $with = ['fonctions.competences.savoirfaires.activites'];

    /**
     * Les fonctions qui constituent un parcours
     */
    public function fonctions(): BelongsToMany
    {
        return $this->belongsToMany(Fonction::class, 'fcmcentral_fonction_parcours', 'parcours_id', 'fonction_id')
                ->withTimestamps();
    }

    
    
}
