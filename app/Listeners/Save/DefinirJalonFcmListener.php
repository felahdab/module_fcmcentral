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
        if (!$event->isOfType(UserGeneratedEventTypes::DEFINIR_JALON_FCM)) {
            return;
        }

        // Recupere le Marin et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();
        $data = $event->detail;
        $fcmMarin = $marin->complementsFcm;

        // Log
        if ($marin && $fcmMarin) {
            Log::info('DefinirJalonFCM appele', ['marinUuid' => $marin->uuid]);

            // ✅ DATE SÉDIMENTATION (peut être null)
            if (array_key_exists('date_sedimentation', $data)) {
                if ($data['date_sedimentation'] === null) {
                    Log::info('Date Sedimentation mise à null');
                    $fcmMarin->date_sedimentation = null;
                } else {
                    Log::info('Date Sedimentation', ['date' => $data['date_sedimentation']]);
                    $fcmMarin->date_sedimentation = Carbon::parse($data['date_sedimentation']);
                }
            }

            // ✅ DATE FCM (peut être null)
            if (array_key_exists('date_fcm', $data)) {
                if ($data['date_fcm'] === null) {
                    Log::info('Date FCM mise à null');
                    $fcmMarin->date_fcm = null;
                } else {
                    Log::info('Date FCM', ['date' => $data['date_fcm']]);
                    $fcmMarin->date_fcm = Carbon::parse($data['date_fcm']);
                }
            }

            // ✅ DATE COMPÉTENCE (peut être null)
            if (array_key_exists('date_competence', $data)) {
                if ($data['date_competence'] === null) {
                    Log::info('Date Competence mise à null');
                    $fcmMarin->date_competence = null;
                } else {
                    Log::info('Date Competence', ['date' => $data['date_competence']]);
                    $fcmMarin->date_competence = Carbon::parse($data['date_competence']);
                }
            }

            // ✅ GESTION DU GEL
            if (array_key_exists('date_debut_gel', $data) && $data['date_debut_gel']) {
                // Créer un nouveau gel
                $marin->etats()->create([
                    'type_etat_id' => $data['type_gel_id'] ?? null,
                    'libelle' => $data['motif_gel'] ?? '',
                    'date_debut' => Carbon::parse($data['date_debut_gel']),
                    'date_fin' => null,
                ]);
                Log::info('Nouveau gel créé', ['marinUuid' => $marin->uuid]);
            }

            if (array_key_exists('date_fin_gel', $data) && $data['date_fin_gel']) {
                // Clôturer le gel actif
                $gelActif = $marin->gelActif();
                if ($gelActif) {
                    $gelActif->update([
                        'date_fin' => Carbon::parse($data['date_fin_gel']),
                    ]);
                    Log::info('Gel clôturé', ['marinUuid' => $marin->uuid]);
                }
            }


            $fcmMarin->save();

            $notification = UserGeneratedEventNotifications::getMess(
                UserGeneratedEventNotifications::DEFINIR_JALON_FCM, 
                $marin->nom
            );

            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();
        } elseif ($marin) {
            Log::warning('Aucun fcmMarin trouvé pour le marin UUID :' . $marin->uuid);
        } else {
            Log::warning('Aucun Marin trouvé UUID :' . $event->object_uuid);
        }
    }
}

