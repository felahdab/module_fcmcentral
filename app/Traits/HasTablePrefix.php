<?php

namespace Modules\FcmCentral\Traits;

use App\Traits\HasTablePrefix as BasePrefixTrait;

trait HasTablePrefix
{
    use BasePrefixTrait;

    protected $prefix = 'fcmcentral_';

   
     /**
     *  Retourne le prefix actuel
     */
    public function getTablePrefix(): string
    {
        return $this->prefix;
    }


    /**
     *  Verifie si le prefix est "fcmcentral_"
     */
    public function isFcmCentralPrefix(): bool
    {
        return $this->prefix === 'fcmcentral_';
    }
    
}