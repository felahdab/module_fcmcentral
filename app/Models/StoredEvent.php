<?php

namespace Modules\FcmCentral\Models;

use Modules\FcmCommun\Models\StoredEvent as StoredEventBase;
use Modules\FcmCentral\Traits\HasTablePrefix;

class StoredEvent extends StoredEventBase
{
    protected $prefix = 'fcmcentral_';
}
