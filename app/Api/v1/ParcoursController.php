<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;

class ParcoursController extends Controller
{

    /**
    * @OA\Get(
    *   path= "/api/fcmcentral/v1/parcours",
    *   security={{"api token": {}}},
    *   @OA\Response(response= 200, description= "La liste des parcours existants.")
    * )
    */
    public function index()
    {
          $parcours = Parcours::all()
            ->makeHidden(['parcours', 'created_at', 'updated_at', 'fonctions']);

          return $parcours;
    }

    /**
    * @OA\Get(
    *   path= "/api/fcmcentral/v1/parcours/{parcours}",
    *   security={{"api token": {}}},
    *   @OA\Parameter(
    *       name="parcours",
    *       description="The token from an active user session",
    *       required=true,
    *       in="path",
    *     ),
    *   @OA\Response(response= 200, description= "La description du parcours et ses versions successives.")
    * )
    */
    public function description(Parcours $parcours)
    {
        $versions = ParcoursSerialise::where('uuid', $parcours->id)->get();
        return $versions;
    }

        /**
    * @OA\Get(
    *   path= "/api/fcmcentral/v1/tous_parcours_serialises",
    *   security={{"api token": {}}},
    *   @OA\Response(response= 200, description= "La liste des parcours serialises existants avec leur description complete.")
    * )
    */
    public function get_tous_parcours_serialises()
    {
          $parcours = ParcoursSerialise::all();
          return $parcours;
    }


}
