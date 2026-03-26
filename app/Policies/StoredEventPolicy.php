<?php

namespace Modules\FcmCentral\Policies;

use App\Policies\GenericSkeletorPolicy;

class StoredEventPolicy extends GenericSkeletorPolicy
{
   protected $slug='fcmcentral::storedEvent';
}