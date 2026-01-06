<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\TypeEtat as BaseTypeEtat;
use Modules\FcmCentral\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Relations\HasMany;



class TypeEtat extends BaseTypeEtat
{

    use HasTablePrefix;


   
    public function etats(): HasMany
    {
        return $this->hasMany(Etat::class, 'type_etat_id');
    }
    


}