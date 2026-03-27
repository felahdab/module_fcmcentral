<?php

namespace Modules\FcmCentral\Api\v1;

use App\Http\Controllers\Controller;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;

/**
 * @tags Module FCM Central: Parcours
 */
class ParcoursFCMController extends Controller
{
    /**
     * Lister les parcours de FCM.
     */
    public function index()
    {
        return Parcours::all()
            ->makeHidden(['parcours', 'created_at', 'updated_at', 'fonctions'])
        ;
    }

    /**
     * Obtenir la description complète d'un parcours.
     */
    public function description(Parcours $parcours)
    {
        return ParcoursSerialise::where('uuid', $parcours->id)->get();
    }

    /**
     * Obtenir la definition complète de tous les parcours existants.
     */
    public function get_tous_parcours_serialises()
    {
        return ParcoursSerialise::all();
    }
}
