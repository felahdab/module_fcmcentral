<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCentral\Events\UserGeneratedEvent;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;

class ParcoursRetireListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType('parcours_retire')){
            return;
        }

        // $event = new UserGeneratedEvent(
        //     event_type: "parcours_retire",
        //     user_id: auth()->user()->uuid,
        //     object_class: get_class($user),
        //     object_uuid: $user->uuid,
        //     detail: [
        //         "parcoursserialise" => $parcours->id,
        //     ]
        // );

        $u = MarinParcours::where('marin_id', $event->object_uuid)
            ->where('parcours_id', $event->detail['parcoursserialise'])
            ->first();

        $u->delete();

    }
}
