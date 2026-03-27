<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\Etat as BaseEtat;

class Etat extends BaseEtat
{
    use HasTablePrefix;

    public function marin(): BelongsTo
    {
        return $this->belongsTo(Marin::class, 'rh_marin_id');
    }

    public function typeEtat(): BelongsTo
    {
        return $this->belongsTo(TypeEtat::class, 'type_etat_id');
    }

    // public function redacteur(): BelongsTo
    // {
    //     return $this->belongsTo(Marin::class, 'marin_id');
    // }
}
