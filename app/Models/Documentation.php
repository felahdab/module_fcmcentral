<?php

namespace Modules\FcmCentral\Models;


use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Traits\HasTablePrefix;



class Documentation extends Model
{

	use HasTablePrefix;

	protected $fillable = [
		'code',
		'titre', 
		'contenu', 
		'ordre',
		'color',
		'pdf_path',
		'deftube_url',
	];
	

    protected $casts = [
        'droits' => 'array',
    ];
	

	

}
