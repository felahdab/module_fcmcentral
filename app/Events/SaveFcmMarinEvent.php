<?php

namespace Modules\FcmCentral\Events;

use Modules\FcmCommun\Events\SaveFcmMarinEvent as BaseSaveFcmEventCommun;

class SaveFcmMarinEvent extends BaseSaveFcmEventCommun
{
    protected $prefix = 'fcmcentral_'; 
    
}