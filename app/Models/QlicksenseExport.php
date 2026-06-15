<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FcmCentral\Traits\HasTablePrefix;

class QlicksenseExport extends Model
{
    
    use HasTablePrefix;

    protected $fillable = [
        'marin_uuid',
        'grade',
        'nom',
        'prenom',
        'specialite',
        'affectation',
        'cohorte',
        'parcours',
        'gel_fcm',
        'vert_ppa_depuis_le',
        'temps_dans_le_ppa',
        'progression_globale',
        'stage_manquants',
        'mentor_uuid',
        'mentor_grade',
        'mentor_nom',
        'mentor_prenom',
        'mentor_unite',
        'mentor_email',
        'parcoursmarin',
    ];

    protected $casts = [
        'temps_dans_le_ppa' => 'integer',
        'progression_globale' => 'decimal:2',
        'stage_manquants' => 'integer',
    ];
}
