<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;

use Filament\Notifications\Notification;

use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;

use Carbon\Carbon;

class DefinirJalonFcmListener
{

    /**
     * Gere l'evenement.
     * 
     * @param UserGeneratedEvent $event;
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::DEFINIR_JALON_FCM)){
            return;
        }

        //Recupere le Marin  et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();
        $data = $event->detail;
        $fcmMarin = $marin->complementsFcm;
       
        // Recuperer le marin via son UUID
        // $marin_uuid = $event->marin_uuid;
        // $marin = Marin::where('uuid',  $marin_uuid)->first();
        // Log
        if ($marin && $fcmMarin ) {
            Log::info('DefinirJalonFCM appele', ['marinUuid' => $marin->uuid]);
           // Sedimentation
           if (isset($data['date_sedimentation'])){
            Log::info('Date Sedimentation', ['date' => $data['date_sedimentation']]);
            $fcmMarin->date_sedimentation = Carbon::parse($data['date_sedimentation']);
           }
           
           if (isset($data['date_fcm'])){
            Log::info('Date FCM', ['date' => $data['date_fcm']]);
             $fcmMarin->date_fcm = Carbon::parse($data['date_fcm']);
           }

           if (isset($data['date_competence'])){
            Log::info('Date Competence', ['date' => $data['date_competence']]);
            $fcmMarin->date_competence = Carbon::parse($data['date_competence']);
            }

            $fcmMarin->save();

           
            
            $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::DEFINIR_JALON_FCM, $marin->nom);

            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();
        } elseif ($marin) {
            Log::warning('Aucun fcmMarin trouvé pour le marin  UUID :' .  $marin->uuid);
        } 
        else {
            Log::warning('Aucun Marin trouvé  UUID :' .  $event->object_uuid);
        }
    }
}
