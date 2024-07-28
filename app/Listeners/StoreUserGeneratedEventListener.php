<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

use Modules\FcmCentral\Models\StoredEvent;

class StoreUserGeneratedEventListener
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
    public function handle(UserGeneratedEvent|RemoteGeneratedEvent $event): void
    {
        StoredEvent::createFromUserGeneratedEvent($event);
    }
}
