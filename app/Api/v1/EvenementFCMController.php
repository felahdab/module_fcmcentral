<?php

namespace Modules\FcmCentral\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;

/**
 * @tags Module FCM Central: Evenements
 */
class EvenementFCMController extends Controller
{
    /**
     *   Déposer un evènement relatif à la FCM.
     */
    public function postevent(UserGeneratedEvent $dto)
    {
        return response('Hello World', 200)->header('Content-Type', 'application/json');
        Log::warning($dto->toJson());
    }
}
