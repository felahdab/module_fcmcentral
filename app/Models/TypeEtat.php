<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Modules\FcmCommun\Models\TypeEtat as BaseTypeEtat;

class TypeEtat extends BaseTypeEtat
{
    use HasTablePrefix;

    public function etats(): HasMany
    {
        return $this->hasMany(Etat::class, 'type_etat_id');
    }
}
