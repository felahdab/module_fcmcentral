<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Enums\Constants;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;

use Modules\FcmCentral\Events\UserGeneratedEvent;

class FcmMarinListener
{

    /**
     * Gere l'evenement.
     * 
     * @param UserGeneratedEvent $event;
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (
            !$event->isOfType(UserGeneratedEventTypes::MARIN_EN_FCM) &&
            !$event->isOfType(UserGeneratedEventTypes::MARIN_PAS_FCM)
        ) {
            return;
        }
        
        $marin = Marin::where('uuid', $event->object_uuid)->first();

        if ($marin) {
            Log::info('Marin trouvé :', ['id' => $marin->id]);

            /**
             * Si l'évènement demande à retirer le marin du suivi de la FCM est qu'il est effectivement suivi.
             */
            if ($event->isOfType(UserGeneratedEventTypes::MARIN_PAS_FCM) && $marin->suivi_en_fcm) {

                $marin->suivi_en_fcm = false;
                $marin->lignes_dans_fcm_marin()->delete();

                // Log de l'action
                Log::info('Le marin a été retiré du suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = Constants::getMess(Constants::MARIN_PAS_FCM, $marin->nom);

                // Notification
                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send();

            } 

            /**
             * Si l'évènement demande la mise en suivi et que le marin n'est pas déjà suivi:
             */
            if ( $event->isOfType(UserGeneratedEventTypes::MARIN_EN_FCM) && ! $marin->suivi_en_fcm) {

                $marin->suivi_en_fcm = true;
                FcmMarin::create(["rh_marin_id" => $marin->id]);

                // Log de l'action
                Log::info('Le marin a été ajouté au suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = Constants::getMess(Constants::MARIN_EN_FCM, $marin->nom);

                // Notification
                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send();

                // Retour Log
                Log::info($marin->prefixTable . 'fcm_marin  :', [
                    'rh_marin_id' => $marin->id,
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                    //'cohorte_id' => $data['cohorte_id'] ?? null,
                    //'mentor_id' =>   $data['mentor_id'] ?? null,
                ]);
            }
        } else {
            Log::warning('Aucun Marin trouvé avec UUID : ' . $event->object_uuid);
        }
    }
}
