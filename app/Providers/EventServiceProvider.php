<?php

namespace Modules\FcmCentral\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Events\SaveFcmMarinEvent;
use Modules\FcmCentral\Events\SuivreMarinFcmEvent;
use Modules\FcmCentral\Events\AssignerMarinParcoursEvent;
use Modules\FcmCentral\Listeners\ParcoursAttribueListener;
use Modules\FcmCentral\Listeners\ParcoursRetireListener;

use Modules\FcmCentral\Listeners\ValideActiviteListener;
use Modules\FcmCentral\Listeners\ValideSavoirFaireListener;

use Modules\FcmCentral\Listeners\TransmitUserGeneratedEventListener;

use Modules\FcmCentral\Listeners\StoreUserGeneratedEventListener;

use Modules\FcmCommun\Listeners\Save\SaveFcmMarinListener;
use Modules\FcmCommun\Listeners\Save\SaveSuivreMarinFcmListener;
use Modules\FcmCommun\Listeners\Save\SaveAssignerMarinParcoursListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserGeneratedEvent::class => [
            ParcoursAttribueListener::class,
            ParcoursRetireListener::class,
            StoreUserGeneratedEventListener::class,
            ValideActiviteListener::class,
            ValideSavoirFaireListener::class,
            TransmitUserGeneratedEventListener::class
        ],

        // Methode dans FcmCommun pour attribuer une cohorte, un mentor dans les tables FCM et sauvegarder aussi le status en FCM dans RH
        SaveFcmMarinEvent::class => [
            SaveFcmMarinListener::class,
        ],

        // Methode dans FcmCommun pour changer le status de FCM dans RH
        SuivreMarinFcmEvent::class => [
            SaveSuivreMarinFcmListener::class,
        ],

        // Methode dans FcmCommun pour changer attribuer un parcours a un marin
        AssignerMarinParcoursEvent::class => [
            SaveAssignerMarinParcoursListener::class,
        ],


    ];
}
