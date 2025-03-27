<?php

namespace Modules\FcmCentral\Events;

use Modules\FcmCommun\Events\AssignerMarinParcoursEvent as AssignerMarinParcoursEventCommun;

class AssignerMarinParcoursEvent  extends AssignerMarinParcoursEventCommun
{
    protected $prefix = 'fcmcentral_'; 
   
}
