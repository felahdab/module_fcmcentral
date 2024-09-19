<?php

namespace Modules\FcmCentral\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\Listeners\LocalUserGeneratedEventListener;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\StoredEvent;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * @tags Module FCM Central: Historique
 */
class HistoriqueFCMController extends Controller
{
     /**
      * Récupère l'historique FCM d'un marin
      */
   public function get_marin_history($uuid)
   {
        $user = Marin::where('id', $uuid)->first();
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

        return $history;
   }

}
