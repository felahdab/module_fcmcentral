<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCentral\Traits\HasTablePrefix;

class CompetenceSavoirfaire extends Model
{
    use HasTablePrefix;

    protected $table = 'fcmcentral_competence_savoirfaire';

    protected $fillable = [
        'competence_id',
        'savoirfaire_id',
        'ordre',
        'coeff',
        'duree',
        'an_acquis',
        'mod_acquis',
    ];

    /**
     * Relation vers la compétence
     */
    public function competence()
    {
        return $this->belongsTo(Competence::class);
    }

    /**
     * Relation vers le savoir-faire
     */
    public function savoirfaire()
    {
        return $this->belongsTo(Savoirfaire::class);
    }

    /**
     * Relation vers le mode d'acquisition
     */
    public function modAcquis()
    {
        return $this->belongsTo(ModAcquis::class, 'mod_acquis', 'code');
    }

    /**
     * Relation vers l'année d'acquisition
     */
    public function anAcquis()
    {
        return $this->belongsTo(AnAcquis::class, 'an_acquis', 'code');
    }
}
