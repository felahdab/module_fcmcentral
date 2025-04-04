<?php

namespace Modules\FcmCentral\Events;
use Modules\FcmCentral\Traits\HasTablePrefix;

use Modules\FcmCommun\Events\AssignerMarinParcoursEvent as AssignerMarinParcoursEventCommun;

class AssignerMarinParcoursEvent  extends AssignerMarinParcoursEventCommun
{
    use HasTablePrefix;
   
}
