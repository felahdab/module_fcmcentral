<?php

namespace Modules\FcmCentral\Events;
use Modules\FcmCentral\Traits\HasTablePrefix;

use Modules\FcmCommun\Events\SuivreMarinFcmEvent as SuivreMarinFcmEventCommun;

class SuivreMarinFcmEvent extends SuivreMarinFcmEventCommun
{
    use HasTablePrefix;
   
}
