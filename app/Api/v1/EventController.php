<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\Listeners\LocalUserGeneratedEventListener;

use Modules\FcmCentral\Models\User;
use Modules\FcmCentral\Models\StoredEvent;

class EventController extends Controller
{

    /**
    * @OA\Post(
    *   path= "/api/v1/postevent",
    *   security={{"api token": {}}},
    *   @OA\RequestBody(required=true, 
    *                                description= "Evenement serialise",
    *                                @OA\JsonContent(required={"email", "password"})),
    *   @OA\Response(response= 200, description= "Depose un evenement genere dans une instance distante.")
    * )
    */
    public function postevent(UserGeneratedEvent $dto)
    {
        // $listener = new LocalUserGeneratedEventListener();

        // $listener->handle($dto);
        //return response('Hello World', 500)->header('Content-Type', 'application/json');

        return response('Hello World', 200)->header('Content-Type', 'application/json');

        Log::warning($dto->toJson());
    }

}
