<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCentral\Events\UserGeneratedEvent;

use Modules\FcmCentral\Models\MarinParcours;

use Modules\FcmCommun\DataObjects\ParcoursDto;

use Illuminate\Support\Arr;


class ValideActiviteListener
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
        if (! $event->isOfType('valide_activite') && ! $event->isOfType('invalide_activite') ){
            return;
        }

        // $event = new static::$UserGeneratedEvent(
        //     event_type: $changes[$activite->id]["state"]["checked"] == "set" ? "valide_activite" : "invalide_activite",
        //     user_id: auth()->user()->uuid,
        //     object_class: "Marin", 
        //     object_uuid: $marinparcours->marin_id,
        //     detail: [
        //         "marinparcours_id" => $marinparcours->id,
        //         "parcours_id" => $marinparcours->parcours_id,
        //         "activite_id" => $activite->id,
        //         "commentaire" => $commentaire
        //     ]
        // );

        $marinparcours = MarinParcours::where('marin_id', $event->object_uuid)
            ->where('parcours_id', $event->detail['parcours_id'])
            ->first();
        
        $parcours = ParcoursDto::from($marinparcours->parcours);

        foreach($parcours->fonctions as $fonction){
            foreach($fonction->competences as $competence){
                foreach ($competence->savoirfaires as $savoirfaire){
                    foreach($savoirfaire->activites as $activite){
                        if ( $activite->id == $event->detail["activite_id"]){
                            Arr::set($activite->state, "checked", $event->event_type == "valide_activite");
                            Arr::set($activite->state, "commentaire", $event->detail["commentaire"] );
                        }
                    }
                }
            }
        }
        $marinparcours->parcours=$parcours;
        $marinparcours->save();
    }
}
