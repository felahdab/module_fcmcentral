<?php

namespace Modules\FcmCentral\Events;
use Modules\FcmCentral\Traits\HasTablePrefix;

use Modules\FcmCommun\Events\SerializeParcoursEvent as SerializeParcoursEventCommun;

class SerializeParcoursEvent  extends SerializeParcoursEventCommun
{
    use HasTablePrefix;
   
}
