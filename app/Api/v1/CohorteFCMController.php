<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCommun\Models\Cohorte;

/**
 * @tags Module FCM Central: Cohortes
 */
class CohorteFCMController extends Controller
{

    /**
    *   Renvoie la liste des cohortes connues.
    **/
    public function index()
    {
        /**
         * FIX: regarder s'il n'y a pas une méthode plus simple pour retirer la colone ID du résultat renvoyé
         * au demandeur...
         */
        $cohorte = new Cohorte;
        $existingColumns = $cohorte->getConnection()->getSchemaBuilder()->getColumnListing($cohorte->getTable());

        $ret = Cohorte::query()->select(array_diff($existingColumns, ["id"]))->get();
        return $ret;
    }

}
