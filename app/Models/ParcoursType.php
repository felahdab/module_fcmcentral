<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\FcmCentral\Database\Factories\ParcoursTypeFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;

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

    /**
     * Relation avec les savoir-faire.
     */
    public function parcours()
    {
        return $this->hasMany(Parcours::class, 'parcours_type_id');
    }

    // Factory
    protected static function newFactory(): ParcoursTypeFactory
    {
        return ParcoursTypeFactory::new();
    }
}
