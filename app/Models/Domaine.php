<?php

namespace Modules\FcmCentral\Models;


use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Traits\HasTablePrefix;



class Domaine extends Model
{

	use HasTablePrefix;

	protected $fillable = [
		'code',
		'libelle_court', 
		'libelle_long', 
		'ordre',
		'color',
	];
	

	    /**
     * Relation avec les savoir-faire
     */
    public function savoirfaires()
    {
        return $this->hasMany(Savoirfaire::class, 'domaine_id');
    }

	

}
