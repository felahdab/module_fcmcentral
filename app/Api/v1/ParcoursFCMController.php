<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;

/**
 * @tags Module FCM Central: Parcours
 */
class ParcoursFCMController extends Controller
{
    /**
     * Lister les parcours de FCM
     */
    public function index()
    {
          $parcours = Parcours::all()
            ->makeHidden(['parcours', 'created_at', 'updated_at', 'fonctions']);

          return $parcours;
    }

    /**
     * Obtenir la description complète d'un parcours
     */
    public function description(Parcours $parcours)
    {
        $versions = ParcoursSerialise::where('uuid', $parcours->id)->get();
        return $versions;
    }

    /**
     * Obtenir la definition complète de tous les parcours existants
     */
    public function get_tous_parcours_serialises()
    {
          $parcours = ParcoursSerialise::all();
          return $parcours;
    }


}
