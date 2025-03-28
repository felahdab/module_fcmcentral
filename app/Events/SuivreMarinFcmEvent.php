<?php

namespace Modules\FcmCentral\Events;

use Modules\FcmCommun\Events\SuivreMarinFcmEvent as SuivreMarinFcmEventCommun;

class SuivreMarinFcmEvent extends SuivreMarinFcmEventCommun
{
    protected $prefix = 'fcmcentral_'; 
   
}
