<?php

namespace Modules\FcmCentral\Events;
use Modules\FcmCentral\Traits\HasTablePrefix;

use Modules\FcmCommun\Events\SaveFcmMarinEvent as BaseSaveFcmEventCommun;

class SaveFcmMarinEvent extends BaseSaveFcmEventCommun
{
    use HasTablePrefix;
    
}