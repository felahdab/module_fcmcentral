<?php

namespace Modules\FcmCentral\Listeners;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;

class StoreUserGeneratedEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(RemoteGeneratedEvent|UserGeneratedEvent $event): void
    {
        StoredEvent::createFromUserGeneratedEvent($event);
    }
}
