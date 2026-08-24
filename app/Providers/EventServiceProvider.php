<?php

namespace Modules\FcmCentral\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin as FcmCentralMarin;
use Modules\FcmCentral\Observers\MarinUniteObserver;

use Modules\FcmCentral\Listeners\Save\CohorteMarinListener;
use Modules\FcmCentral\Listeners\Save\CreationParcoursSerialiseListener;
use Modules\FcmCentral\Listeners\Save\DefinirJalonFcmListener;
use Modules\FcmCentral\Listeners\Save\FcmMarinListener;
use Modules\FcmCentral\Listeners\Save\MarinParcours\AnnulElementMarinParcoursListener;
use Modules\FcmCentral\Listeners\Save\MarinParcours\AnnulProposElementMarinParcoursListener;
use Modules\FcmCentral\Listeners\Save\MarinParcours\PieceJointeMarinParcoursListener;
use Modules\FcmCentral\Listeners\Save\MarinParcours\ProposElementMarinParcoursListener;
use Modules\FcmCentral\Listeners\Save\MarinParcours\ValidElementMarinParcoursListener;
use Modules\FcmCentral\Listeners\Save\MentorMarinListener;
use Modules\FcmCentral\Listeners\Save\ParcoursSerialiseMarinListener;
use Modules\FcmCentral\Listeners\StoreUserGeneratedEventListener;
use Modules\FcmCentral\Listeners\TransmitUserGeneratedEventListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserGeneratedEvent::class => [
            StoreUserGeneratedEventListener::class,
            FcmMarinListener::class,
            CohorteMarinListener::class,
            MentorMarinListener::class,
            CreationParcoursSerialiseListener::class,
            ParcoursSerialiseMarinListener::class,
            ValidElementMarinParcoursListener::class,
            AnnulElementMarinParcoursListener::class,
            ProposElementMarinParcoursListener::class,
            AnnulProposElementMarinParcoursListener::class,
            DefinirJalonFcmListener::class,
            PieceJointeMarinParcoursListener::class,
            // TransmitUserGeneratedEventListener::class,
        ],

        // // Methode dans FcmCommun pour attribuer une cohorte, un mentor dans les tables FCM et sauvegarder aussi le status en FCM dans RH
        // SaveFcmMarinEvent::class => [
        //     SaveFcmMarinListener::class,
        // ],

        // // Methode dans FcmCommun pour changer le status de FCM dans RH
        // SuivreMarinFcmEvent::class => [
        //     SaveSuivreMarinFcmListener::class,
        // ],

        // // Methode dans FcmCommun pour changer attribuer un parcours a un marin
        // AssignerMarinParcoursEvent::class => [
        //     SaveAssignerMarinParcoursListener::class,
        // ],

        //  // Methode dans FcmCommun pour serialiser le Parcours des  tables FCM_Central_XXX
        //  SerializeParcoursEvent::class => [
        //     SaveSerializeParcoursListener::class,
        // ],
    ];

    public function boot(): void
    {
        parent::boot();

        // Les modèles RH\Marin, FcmCommun\Marin et FcmCentral\Marin représentent la même
        // ligne en base (héritage Eloquent), mais les événements Eloquent sont scopés à la
        // classe réellement instanciée : il faut donc observer chacune des 3 classes pour
        // détecter un changement d'unité quel que soit le modèle utilisé pour le sauvegarder.
      
        FcmCentralMarin::observe(MarinUniteObserver::class);
    }
}
