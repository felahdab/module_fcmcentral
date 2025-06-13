<?php

namespace Modules\FcmCentral\Models;


use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Traits\HasTablePrefix;



class Domaine extends Model
{

	use HasTablePrefix;

	protected $fillable = [
		'id',
		'libelle_court', 
		'libelle_long', 
	];
	
	

}
