<?php

namespace Modules\FcmCentral\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCentral\Listeners\ParcoursAttribueListener;
use Modules\FcmCentral\Listeners\ParcoursRetireListener;

use Modules\FcmCentral\Listeners\StoreUserGeneratedEventListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserGeneratedEvent::class => [
            ParcoursAttribueListener::class,
            ParcoursRetireListener::class,
            StoreUserGeneratedEventListener::class
        ],
    ];
}
