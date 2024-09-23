<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCentral\Events\UserGeneratedEvent;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;

class ParcoursAttribueListener
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
        if (! $event->isOfType('attribue_parcours')){
            return;
        }

        // $event = new UserGeneratedEvent(
        //     event_type: "attribue_parcours",
        //     user_id: auth()->user()->uuid,
        //     object_class: get_class($user),
        //     object_uuid: $user->uuid,
        //     detail: [
        //         "parcoursserialise" => $parcours->id,
        //         "parcours" => $parcours->uuid,
        //         "version" => $parcours->version,
        //     ]
        // );

        $u = new MarinParcours();
        $u->marin_id = $event->object_uuid;
        $u->parcours_id = $event->detail["parcoursserialise"];

        $p = ParcoursSerialise::find($event->detail['parcoursserialise']);

        $u->parcours = $p->parcours;

        $u->save();
    }
}
