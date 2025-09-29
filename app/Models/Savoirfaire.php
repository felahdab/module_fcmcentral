<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Database\Factories\SavoirfaireFactory;
use Modules\FcmCentral\Models\ActiviteSavoirfairePivot;

use Modules\FcmCentral\Traits\HasTablePrefix;


class Savoirfaire extends Model
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
        "code_sicomp",
        //"coeff",
        //"duree",
        //"an_acquie",
        "ordre",
        //"mod_acquis",
        "domaine_id",
    ];
    
    protected $casts =[
        'data' => 'array',
    ];
    
    protected static function newFactory(): SavoirfaireFactory
    {
        return Savoirfairefactory::new();
    }

    public function competences(): BelongsToMany
    {
        return $this->belongsToMany(Competence::class, 'fcmcentral_competence_savoirfaire', 'savoirfaire_id', 'competence_id')
            ->withPivot('coeff', 'duree', 'ordre', 'an_acquis', 'mod_acquis')
            ->withTimestamps();
    }

    public function activites(): BelongsToMany
    {
        return $this->belongsToMany(Activite::class, 'fcmcentral_activite_savoirfaire', 'savoirfaire_id',  'activite_id')
                ->withPivot('coeff', 'duree', 'ordre','data')
                ->withTimestamps()
                ->using(ActiviteSavoirfairePivot::class);
    }

    public function domaine()
    {
        return $this->belongsTo(Domaine::class, "domaine_id", "id");
    }

    public function competencePrincipale()
    {
    return $this->belongsTo(Competence::class, 'competence_id');
    }

    public function activitesPrincipaux()
    {
        return $this->hasMany(Activite::class, 'savoirfaire_id');
    }

}
