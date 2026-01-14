<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;

use Filament\Notifications\Notification;

use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;

class MentorMarinListener
{

    /**
     * Gere l'evenement.
     * 
     * @param UserGeneratedEvent $event;
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN)){
            return;
        }

        //Recupere le Marin  et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();
        $data = $event->detail;
        $mentor = Marin::where('uuid', $data["mentor"])->first();

        // Recuperer le marin via son UUID
        // $marin_uuid = $event->marin_uuid;
        // $marin = Marin::where('uuid',  $marin_uuid)->first();
        // Log
        if ($marin) {
            Log::info('MentorMarinListener appele', ['marinUuid' => $marin->uuid]);
            Log::info('Marin trouvé :', ['id' => $marin->id]);

            // FcmCentral
            FcmMarin::updateorCreate(
                // Condition pour trouver le marin
                ['rh_marin_id' => $marin->id],
                [
                    'mentor_id' =>   $mentor->id,
                ],
            );
            
            $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::MENTOR, $marin->nom);

            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();
        } else {
            Log::warning('Aucun Marin trouvé  UUID :' .  $event->object_uuid);
        }
    }
}
