<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\Note as BaseNote;

class Note extends BaseNote
{
    use HasTablePrefix;

    public function marin(): BelongsTo
    {
        return $this->belongsTo(Marin::class, 'rh_marin_id');
    }

    public function typeNote(): BelongsTo
    {
        return $this->belongsTo(TypeNote::class, 'type_note_id');
    }

    public function redacteur(): BelongsTo
    {
        return $this->belongsTo(Marin::class, 'redacteur');
    }
}
