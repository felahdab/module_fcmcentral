<?php

namespace Modules\FcmCentral\Api\v1;

use App\Http\Controllers\Controller;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\StoredEvent;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @tags Module FCM Central: Historique
 */
class HistoriqueFCMController extends Controller
{
    /**
     * Récupère l'historique FCM d'un marin.
     *
     * @param mixed $uuid
     */
    public function get_marin_history($uuid)
    {
        $user = Marin::where('id', $uuid)->first();
        if (null == $user) {
            return response(['error' => 'user not found'], 400);
        }

        $history = StoredEvent::where('object_uuid', $uuid)
            ->orderBy('event_datetime', 'asc')
        ;

        return QueryBuilder::for($history)
            ->allowedFilters([
                'event_uuid',
                AllowedFilter::scope('after'),
                AllowedFilter::scope('before'),
            ])
            ->get()
        ;
    }
}
