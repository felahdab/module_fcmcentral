<?php

namespace Modules\FcmCentral\Listeners;

use Modules\FcmCommun\Listeners\Save\SaveAssignerMarinParcoursListener as BaseListener;

use Modules\FcmCentral\Models\FcmMarin;

class SaveAssignerMarinParcoursListener extends BaseListener
{
    public $fcmmarinclass = FcmMarin::class;

}
