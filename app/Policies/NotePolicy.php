<?php

namespace Modules\FcmCentral\Policies;

use App\Policies\GenericSkeletorPolicy;

class NotePolicy extends GenericSkeletorPolicy
{
    protected $slug = 'fcmcentral::note';
}
