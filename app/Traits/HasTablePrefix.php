<?php

namespace Modules\FcmCentral\Traits;

use App\Traits\HasTablePrefix as BasePrefixTrait;

trait HasTablePrefix
{
    use BasePrefixTrait;

    protected $prefix = 'fcmcentral_';
}