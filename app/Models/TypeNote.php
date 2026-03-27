<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\TypeNote as BaseTypeNote;

class TypeNote extends BaseTypeNote
{
    use HasTablePrefix;

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'type_note_id');
    }
}
