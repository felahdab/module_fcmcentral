<?php

namespace Modules\FcmCentral\Listeners\Save;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\TypeEtat;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class DefinirJalonFcmListener
{
    /**
     * Gere l'evenement.
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
                if (null === $data['date_sedimentation']) {
                    Log::info('Date Sedimentation mise à null');
                    $fcmMarin->date_sedimentation = null;
                } else {
                    Log::info('Date Sedimentation', ['date' => $data['date_sedimentation']]);
                    $fcmMarin->date_sedimentation = Carbon::parse($data['date_sedimentation']);
                }
            }

            // ✅ DATE FCM (peut être null)
            if (array_key_exists('date_fcm', $data)) {
                if (null === $data['date_fcm']) {
                    Log::info('Date FCM mise à null');
                    $fcmMarin->date_fcm = null;
                } else {
                    Log::info('Date FCM', ['date' => $data['date_fcm']]);
                    $fcmMarin->date_fcm = Carbon::parse($data['date_fcm']);
                }
            }

            // ✅ DATE COMPÉTENCE (peut être null)
            if (array_key_exists('date_competence', $data)) {
                if (null === $data['date_competence']) {
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
                // dd($data);
                /**
                 * On a prévu de s'appuyer sur un modèle pour anticiper des évolutions futures.
                 */
                $etatTypeId = TypeEtat::where('uuid', $data['type_gel_uuid'])->first()?->id;
                // $etatTypeId = TypeEtat::where('uuid', $data['type_gel_uuid'])->first()?->id;
                $marin->etats()->create([
                    'type_etat_id' => $etatTypeId ?? null,
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
                ->send()
            ;
        } elseif ($marin) {
            Log::warning('Aucun fcmMarin trouvé pour le marin UUID :'.$marin->uuid);
        } else {
            Log::warning('Aucun Marin trouvé UUID :'.$event->object_uuid);
        }
    }
}
