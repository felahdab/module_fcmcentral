<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Modules\FcmCentral\Events\UserGeneratedEvent;

use Modules\FcmCentral\Models\MarinParcours;

use Modules\FcmCommun\DataObjects\ParcoursDto;

use Illuminate\Support\Arr;


class ValideSavoirFaireListener
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
        if (! $event->isOfType('valide_savoirfaire') && ! $event->isOfType('invalide_savoirfaire') ){
            return;
        }

        // $event = new static::$UserGeneratedEvent(
        //     event_type: $changes[$savoirfaire->id]["state"]["checked"] == "set" ? "valide_savoirfaire" : "invalide_savoirfaire",
        //     user_id: auth()->user()->uuid,
        //     object_class: "Marin", 
        //     object_uuid: $marinparcours->marin_id,
        //     detail: [
        //         "marinparcours_id" => $marinparcours->id,
        //         "parcours_id" => $marinparcours->parcours_id,
        //         "savoirfaire_id" => $savoirfaire->id,
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
                        if ( $savoirfaire->id == $event->detail["savoirfaire_id"]){
                            Arr::set($savoirfaire->state, "checked", $event->event_type == "valide_savoirfaire");
                            Arr::set($savoirfaire->state, "commentaire", $event->detail["commentaire"] );

                        }
                }
            }
        }
        $marinparcours->parcours=$parcours;
        $marinparcours->save();
    }
}
