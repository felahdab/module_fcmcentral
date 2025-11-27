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

        // === RETRAIT DU SUIVI FCM ===
        if ($event->isOfType(UserGeneratedEventTypes::MARIN_PAS_FCM) && $marin->suivi_en_fcm) {

            $marin->suivi_en_fcm = false;
            // $marin->lignes_dans_fcm_marin()->delete(); // Désactivé, on garde l'historique

            // Historiser l'état "Fin FCM"
            $fcmMarin = $marin->complementsFcm;
            if ($fcmMarin) {
                $fcmMarin->historiserEtat('FCM_fin');
            }

            Log::info('Le marin a été retiré du suivi FCM', [
                'nom' => $marin->nom,
                'uuid' => $marin->uuid,
            ]);

            $notification = Constants::getMess(Constants::MARIN_PAS_FCM, $marin->nom);

            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();
        }

        // === AJOUT AU SUIVI FCM ===
        if ($event->isOfType(UserGeneratedEventTypes::MARIN_EN_FCM) && !$marin->suivi_en_fcm) {

            // Si pas encore FcmMarin : création + état "En Sedimentation"
            if (!$marin->complementsFcm) {
                $fcmMarin = FcmMarin::create(["rh_marin_id" => $marin->id]);
                $fcmMarin->historiserEtat('FCM_sed');
            } else {
                // Si déjà FcmMarin : état "En FCM Actif"
                $marin->complementsFcm->historiserEtat('FCM_act');
            }

            Log::info('Le marin a été ajouté au suivi FCM', [
                'nom' => $marin->nom,
                'uuid' => $marin->uuid,
            ]);

            $notification = Constants::getMess(Constants::MARIN_EN_FCM, $marin->nom);

            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();

            Log::info($marin->prefixTable . 'fcm_marin  :', [
                'rh_marin_id' => $marin->id,
                'nom' => $marin->nom,
                'uuid' => $marin->uuid,
            ]);
        }
    } else {
        Log::warning('Aucun Marin trouvé avec UUID : ' . $event->object_uuid);
    }
}

}
