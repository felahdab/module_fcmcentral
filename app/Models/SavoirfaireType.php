<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCentral\Traits\HasTablePrefix;

class SavoirfaireType extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'id',
        'code',
        'libelle_court',
        'libelle_long',
        'ordre',
        'color',
    ];

    /**
     * Relation avec les savoir-faire.
     */
    public function competences()
    {
        return $this->hasMany(CompetenceSavoirfaire::class, 'savoirfaire_type_id');
    }
}
