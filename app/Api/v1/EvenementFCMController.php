<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\Listeners\LocalUserGeneratedEventListener;

use Modules\FcmCentral\Models\User;
use Modules\FcmCentral\Models\StoredEvent;

/**
 * @tags Module FCM Central: Evenements
 */
class EvenementFCMController extends Controller
{

    /**
    *   Déposer un evènement relatif à la FCM.
    **/
    public function postevent(UserGeneratedEvent $dto)
    {
        return response('Hello World', 200)->header('Content-Type', 'application/json');

        Log::warning($dto->toJson());
    }

}
