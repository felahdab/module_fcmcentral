<?php

namespace Modules\FcmCentral\Http\Livewire;

use Modules\FcmCommun\Http\Livewire\Livret as BaseLivretDeTransformation;

use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

class LivretDeTransformation extends BaseLivretDeTransformation
{
    public static $ParcoursService = ParcoursService::class;
    public static $ParcoursSerialise = ParcoursSerialise::class;
    public static $UserParcours = UserParcours::class;
}
