<?php

namespace Modules\FcmCentral\Listeners\Save;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class MentorMarinListener
{
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN)) {
            return;
        }

        $marin = Marin::where('uuid', $event->object_uuid)->first();
        $data = $event->detail;

        if (!$marin) {
            Log::warning('MentorMarinListener : aucun Marin trouvé UUID : '.$event->object_uuid);

            return;
        }

        Log::info('MentorMarinListener appelé', ['marinUuid' => $marin->uuid]);

        $mentorsUUIDs = Arr::wrap($data['mentor']);

        // Résoudre les UUIDs des mentors en IDs
        $mentorIds = Marin::whereIn('uuid', $mentorsUUIDs)
            ->pluck('id')
            ->toArray();

        // Récupère ou crée le FcmMarin
        $fcmMarin = FcmMarin::firstOrCreate(['rh_marin_id' => $marin->id]);

        // Sync remplace la liste complète des mentors
        $fcmMarin->mentors()->sync($mentorIds);

        $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::MENTOR, $marin->nom);

        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }
}


