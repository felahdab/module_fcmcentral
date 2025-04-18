<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

use Modules\RH\Models\Marin as RHMarin;
use Modules\FcmCommun\Models\Marin as BaseMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\FcmMarin;


class Marin extends BaseMarin
{
    /**
     * Cette relation est là pour permettre les accès aux lignes de la table FcmMarins associees à ce Marin.
     * Mais il ne doit normalement y avoir qu'une seule ligne comme cela dans cette table.
     */
    public function lignes_dans_fcm_marin()
    {
        return $this->hasMany(FcmMarin::class,'rh_marin_id','id');
    }

    /**
     * Grace à la définition de cet attribut, le developpeur peut utiliser les méthodes suivantes:
     * $marin->complements_fcm->cohorte
     * $marin->complements_fcm->mentor
     * $marin->complements_fcm->donnes_du_parcours->taux_global
     * $marin->complements_fcm->donnes_du_parcours->taux_stage
     * $marin->complements_fcm->donnes_du_parcours->taux_activite
     * $marin->complements_fcm->donnes_du_parcours->parcours_serialise
        
     */
    public function getComplementsFcmAttribute()
    {
        return $this->lignes_dans_fcm_marin->first();
    }

    /**
     * Scope permettant de filtrer les marin en fonction de leur cohorte
     * Exemple: Marin::cohorte($cohorte)->get();
     */
    public function scopeCohorte(Builder $query, Cohorte $cohorte): void
    {
        $query->whereIn('id', FcmMarin::where('cohorte_id', $cohorte->id)->get()->pluck('rh_marin_id'));
    }

    /**
     * Scope permettant de filtrer les marins en fonction de leur mentor.
     * Exemple: Marin::mentorePar($mentor)->get();
     */
    public function scopeMentorePar(Builder $query, Model $marin)
    {
        /**
         * Pour faciliter la vie du développeur, on commence par caster le modele $marin vers la class
         * courante. 
         */
        if ($marin instanceof RHMarin || $marin instanceof BaseMarin){
            $marin = cast_as_eloquent_descendant($marin, static::class);
        }
        /**
         * A partir d'ici, $marin est un objet de la classe FcmCentral\Models\Marin
         * Ce marin désigne le mentor dont on veut trouver tous les marins suivis en FCM.
         */
        $query->whereIn('id', FcmMarin::where('mentor_id', $marin->id)->get()->pluck('rh_marin_id'));
        
    }

    /**
     * Scope permettant de filtrer les marins qui sont des mentors FCM.
     */
    public function scopeMentor(Builder $query)
    {
        $query->whereIn('id', FcmMarin::all()->pluck('mentor_id'));
    }

}