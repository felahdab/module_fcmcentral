<?php

namespace Modules\FcmCentral\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCentral\Database\Factories\ParcoursTypeFactory;



class ParcoursType extends Model
{

	use HasFactory;
    use HasTablePrefix;

	protected $fillable = [
        'code',
		'libelle_court', 
		'libelle_long', 
        'ordre',
        'color',
	];
    
    // Factory
    protected static function newFactory(): ParcoursTypeFactory
    {
        return ParcoursTypeFactory::new();
    }

	    /**
     * Relation avec les savoir-faire
     */
    public function parcours()
    {
        return $this->hasMany(Parcours::class, 'parcours_type_id');
    }

	

}
