<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\Listeners\LocalUserGeneratedEventListener;

use Modules\FcmCentral\Models\User;
use Modules\FcmCentral\Models\StoredEvent;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

use Modules\FcmCentral\Api\v1\Requests\ApiGetUserUuidRequest;

class UserController extends Controller
{
    /**
    * @OA\Get(
    *   path= "/api/fcmcentral/v1/get_user_uuid",
    *   security={{"api token": {}}},
    *   @OA\Parameter(
    *        name="email",
    *        description="user email adress",
    *        required=false,
    *        in="query"
    *         ),
    *   @OA\Parameter(
    *        name="nid",
    *        description="user NID",
    *        required=false,
    *        in="query"
    *         ),
    *   @OA\Response(response= 200, description= "Obtenir le UUID d'un utilisateur.")
    * )
    */
   public function get_user_uuid(ApiGetUserUuidRequest $request)
   {
     if ($request->has('email')){
          $u = User::select(["uuid", "name", "prenom", "nid", "email"])->where('email', $request->input('email'))->get();
          return $u;
     }
     if ($request->has('nid')){
          $u = User::where('nid', $request->input('nid'))->get();
          return $u;
     }
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
    *   @OA\Parameter(
    *       name="filter[event_uuid]",
    *       description="Identifiant unique de l'evenement voulu",
    *       required=false,
    *       in="query",
    *     ),
    *   @OA\Parameter(
    *       name="filter[after]",
    *       description="Date a partir de laquelle on veut les evenements",
    *       required=false,
    *       in="query",
    *     ),
    *   @OA\Parameter(
    *       name="filter[before]",
    *       description="Date avant laquelle on veut les evenements",
    *       required=false,
    *       in="query",
    *     ),
    *   @OA\Response(response= 200, description= "Récupérer l'historique complet d'une utilisateur.")
    * )
    */

   public function get_user_history($uuid)
   {
        $user = User::where('uuid', $uuid)->first();
        if ($user == null) {
          return response(["error" => "user not found"], 400);
        }

        $history = StoredEvent::where("object_uuid", $uuid)
               ->orderBy('event_datetime', 'asc');

        $history = QueryBuilder::for($history)
               ->allowedFilters([
                    'event_uuid', 
                    AllowedFilter::scope('after'),
                    AllowedFilter::scope('before'),
               ])
               ->get();
        // $events = StoredEvent::where('object_class', User::class)
        // ->where('object_uuid', $uuid)
        // ->orderBy('event_timestamp')
        // ->get()
        return $history;
   }

}
