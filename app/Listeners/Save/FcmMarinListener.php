<?php

namespace Modules\FcmCentral\Listeners\Save;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class FcmMarinListener
{
    /**
     * Gere l'evenement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (
            !$event->isOfType(UserGeneratedEventTypes::MARIN_EN_FCM)
            && !$event->isOfType(UserGeneratedEventTypes::MARIN_PAS_FCM)
        ) {
            return;
        }

        $marin = Marin::findByEventReference($event->object_uuid);

        if ($marin) {
            Log::info('Marin trouvé :', ['id' => $marin->id]);
            $data = $event->detail ?? [];

            // === RETRAIT DU SUIVI FCM ===
            if ($event->isOfType(UserGeneratedEventTypes::MARIN_PAS_FCM) && $marin->suivi_en_fcm) {
                // Met a jour dans RH Marin statut enfcm false (methode dans FcmCommun)
                $marin->suivi_en_fcm = false;
                // $marin->lignes_dans_fcm_marin()->delete(); // Désactivé, on garde l'historique

                // Historiser l'état "Fin FCM"
                $fcmMarin = $marin->complementsFcm;

                if ($fcmMarin && $fcmMarin->donnees_du_parcours) {
                    // $marin->historiserEtat('FCM_FIN');
                    $fcmMarin->date_competence = isset($data['date_competence'])
                    ? Carbon::parse($data['date_competence'])
                    : Carbon::now();
                    $fcmMarin->save();
                }

                Log::info('Le marin a été retiré du suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::MARIN_PAS_FCM, $marin->nom);

                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send()
                ;
            }

            // === AJOUT AU SUIVI FCM ===
            if ($event->isOfType(UserGeneratedEventTypes::MARIN_EN_FCM) && !$marin->suivi_en_fcm) {
                // Met a jour dans RH Marin statut enfcm true (methode dans FcmCommun)
                $marin->suivi_en_fcm = true;

                // Si pas encore FcmMarin : création + état "En Sedimentation"
                if (!$marin->complementsFcm) {
                    $fcmMarin = FcmMarin::create(['rh_marin_id' => $marin->id]);

                    // $marin->historiserEtat('FCM_SED');
                    // $fcmMarin->date_sedimentation = isset($data['date_sedimentation'])
                    // ? Carbon::parse($data['date_sedimentation'])
                    // : Carbon::now();
                    $fcmMarin->date_sedimentation = Carbon::now();

                    $fcmMarin->save();
                } else {
                    // Si déjà FcmMarin : état "En FCM Actif"
                    $fcmMarin = $marin->complementsFcm;
                    if ($fcmMarin->donnees_du_parcours) {
                        // $marin->historiserEtat('FCM_ACT');
                        // $fcmMarin->date_fcm = isset($data['date_fcm'])
                        // ? Carbon::parse($data['date_fcm'])
                        // : Carbon::now();
                        // $fcmMarin->save();
                    }
                }

                Log::info('Le marin a été ajouté au suivi FCM', [
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);

                $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::MARIN_EN_FCM, $marin->nom);

                Notification::make()
                    ->title($notification['title'])
                    ->success()
                    ->body($notification['message'])
                    ->send()
                ;

                Log::info($marin->prefixTable.'fcm_marin  :', [
                    'rh_marin_id' => $marin->id,
                    'nom' => $marin->nom,
                    'uuid' => $marin->uuid,
                ]);
            }
        } else {
            Log::warning('Aucun Marin trouvé avec UUID : '.$event->object_uuid);
        }
    }
}
