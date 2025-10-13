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
        'code',
        'libelle_long',
        'libelle_court',
        'parcours_type_id',
        'ordre',
        'color',
    ];

    // protected $casts = [
    //     'parcours' => 'array',
    // ];
    
    protected static function newFactory(): ParcoursFactory
    {
        return ParcoursFactory::new();
    }

   

    public function parcoursType() {
        return $this->belongsTo(ParcoursType::class,'parcours_type_id');
    }


    protected $with = [
        'annees.savoirfaires.objectifs' 
    ];

    /**
     * Les fonctions qui constituent un parcours
     */
    public function annees(): BelongsToMany
    {
        return $this->belongsToMany(Annee::class, 
        'fcmcentral_annee_parcours', 'parcours_id', 'annee_id')
            ->withPivot('ordre')
            ->withTimestamps()
            ->orderBy('fcmcentral_annee_parcours.ordre'); 
    }

    public function parcoursSerialise()
	{
		return $this->hasMany(ParcoursSerialise::class, 'parcours_id', 'id');
	}

    public function getLatestSerializedVersion()
    {
        $last_version = $this->parcoursSerialise()->orderBy('version')->get()->last();
        if ($last_version == null)
        {
            return 0;
        }
        return $last_version->version;
    }
    
    
}
