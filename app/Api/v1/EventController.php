<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\DataObjects\UserGeneratedEvent;

use App\DataObjects\UserGeneratedEventSpatie;

use Illuminate\Support\Facades\Log;

use App\Listeners\LocalUserGeneratedEventListener;

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
        $listener = new LocalUserGeneratedEventListener();

        $listener->handle($dto);

        Log::info($dto == null ? "null" : "pas null");
    }

    /**
    * @OA\Get(
    *   path= "/api/v1/get_user_uuid",
    *   security={{"api token": {}}},
    *   @OA\RequestBody(required=true, 
    *                                description= "Evenement serialise",
    *                                @OA\JsonContent(required={"email", "password"})),
    *   @OA\Response(response= 200, description= "Obtenir le UUID d'un utilisateur.")
    * )
    */
   public function get_user_uuid()
   {
        return ["uuid" => "uuid"];
   }

   /**
    * @OA\Get(
    *   path= "/api/v1/get_user_history",
    *   security={{"api token": {}}},
    *   @OA\RequestBody(required=true, 
    *                                description= "Evenement serialise",
    *                                @OA\JsonContent(required={"email", "password"})),
    *   @OA\Response(response= 200, description= "Récupérer l'historique complet d'une utilisateur.")
    * )
    */

   public function get_user_history(Request $request)
   {
        $uuid = $request->input('uuid');
        // $user = User::findByUuid($uuid);
        // $events = StoredEvent::where('object_class', User::class)
        // ->where('object_uuid', $uuid)
        // ->orderBy('event_timestamp')
        // ->get()
        return ["uuid" => "history"];
   }

}
