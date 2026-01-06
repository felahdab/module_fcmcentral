<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\TypeNote as BaseTypeNote;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Relations\HasMany;



class TypeNote extends BaseTypeNote
{

    use HasTablePrefix;


   
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'type_note_id');
    }
    


}