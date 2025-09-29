<?php

namespace Modules\FcmCentral\Models;


use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Traits\HasTablePrefix;



class AnAcquis extends Model
{

	use HasTablePrefix;

	protected $fillable = [
		'id',
		'code', 
		'libelle_court', 
		'libelle_long', 
	];
	

	/**
     * Relation avec la table de liaison competence_savoirfaire
     */
    public function competenceSavoirfaires()
    {
        return $this->hasMany(CompetenceSavoirfaire::class, 'an_acquis', 'code');
    }

	

}
