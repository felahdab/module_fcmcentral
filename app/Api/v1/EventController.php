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
    *   path= "/api/fcmcentral/v1/get_user_history/{uuid}",
    *   security={{"api token": {}}},
    *   @OA\Parameter(
    *       name="uuid",
    *       description="Identifiant unique de l'utilisateur",
    *       required=true,
    *       in="path",
    *     ),
    *   @OA\Response(response= 200, description= "Récupérer l'historique complet d'une utilisateur.")
    * )
    */

   public function get_user_history($uuid)
   {
        //$uuid = $request->input('uuid');
        $user = User::where('uuid', $uuid)->first();
        if ($user == null) {
          return response(["error" => "user not found"], 400)
                    ->header('Content-Type', 'application/json');;
        }

        $history = StoredEvent::where("object_class", User::class)
          ->where("object_uuid", $uuid)
          ->get();
        // $events = StoredEvent::where('object_class', User::class)
        // ->where('object_uuid', $uuid)
        // ->orderBy('event_timestamp')
        // ->get()
        return [$user->uuid => $history];
   }

}
