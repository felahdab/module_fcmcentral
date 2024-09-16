<?php

namespace Modules\FcmCentral\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Listeners\ParcoursAttribueListener;
use Modules\FcmCentral\Listeners\ParcoursRetireListener;

use Modules\FcmCentral\Listeners\ValideActiviteListener;
use Modules\FcmCentral\Listeners\ValideSavoirFaireListener;

use Modules\FcmCentral\Listeners\StoreUserGeneratedEventListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserGeneratedEvent::class => [
            ParcoursAttribueListener::class,
            ParcoursRetireListener::class,
            StoreUserGeneratedEventListener::class,
            ValideActiviteListener::class,
            ValideSavoirFaireListener::class
        ],
    ];
}
