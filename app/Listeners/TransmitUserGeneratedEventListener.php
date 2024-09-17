<?php

namespace Modules\FcmCentral\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Jobs\Middleware\RateLimited;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\RemoteGeneratedEvent;
use Modules\FcmCentral\Models\StoredEvent;

use App\Models\Setting;

use Modules\FcmCentral\Services\RemoteApiClientService;

class TransmitUserGeneratedEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 0;
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
        $settings = Setting::forKey('fcmcentral');
        if (! $settings->get('transmit_user_generated_events_to_remote_fcmcentral_instance')){
            return;
        }

        $url = $settings->get('url_of_remote_fcmcentral_instance');
        $token = $settings->get('token_for_remote_fcmcentral_instance');

        RemoteApiClientService::push_user_generated_event_to_remote_fcmcentral_instance($url, $token, $event);

    }
 
    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited];
    }
}
