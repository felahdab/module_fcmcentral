<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCommun\Models\Cohorte;

class FcmMarin extends Model
{
    use HasTablePrefix;

    
    // Relation entre FcmCentral FcmMarins et FcmParcoursSerialises
    public function parcoursSerialises(): BelongsToMany
    {
        return $this->belongsToMany(ParcoursSerialise::class, 'fcmcentral_marin_parcours', 'fcmmarin_id', 'parcoursserialise_id')
        ->withPivot('taux_global', 'taux_stage', 'taux_activite','parcoursmarin')
        ->withTimestamps();
    }
   
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'rh_marin_id',
        'data',
        'cohorte_id',
        'mentor_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

  
    public function marin()
    {
        return $this->belongsTo(Marin::class,'rh_marin_id');
    }

    public function cohorte()
    {
        return $this->belongsTo(Cohorte::class);
    }

    public function mentor()
    {
        return $this->belongsTo(Marin::class,'mentor_id');
    }

    /**
     * La relation ci-dessous facilite l'accès aux lignes dans marin_parcours qui sont relative à cet objet.
     * Mais en pratique, il ne doit y avoir qu'une seule ligne comme cela.
     */
    public function lignes_dans_marin_parcours()
    {
        return $this->hasMany(MarinParcours::class, 'fcmmarin_id');
    }

    /**
     * Grace à la définition de cet attribut, le developpeur peut utiliser les méthodes suivantes:
     * $marin->complements_fcm->cohorte
     * $marin->complements_fcm->mentor
     * $marin->complements_fcm->donnes_du_parcours
     */
    public function getDonneesDuParcoursAttribute()
    {
        return $this->lignes_dans_marin_parcours->first();
    }

    public function setCohorteAttribute(Cohorte $cohorte)
    {
        $this->cohorte_id = $cohorte->id;
        $this->save();
    }

    public function setMentorAttrivute(Marin $mentor)
    {
        $this->mentor_id = $mentor->id;
        $this->save();
    }

}