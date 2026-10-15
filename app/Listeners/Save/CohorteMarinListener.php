<?php

namespace Modules\FcmCentral\Listeners\Save;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Cohorte;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class CohorteMarinListener
{
    /**
     * Gere l'evenement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (!$event->isOfType(UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN)) {
            return;
        }

        $marin = Marin::findByEventReference($event->object_uuid);

        $detail = $event->detail;
        $cohorte = Cohorte::where('uuid', $detail['cohorte'])->first();

        // Log
        if ($marin) {
            Log::info('CohorteMarinListener appele', ['marinUuid' => $marin->uuid]);
            Log::info('Marin trouvé :', ['id' => $marin->id]);

            // $fcmMarin =$marinClass::find($cohorte_id);
            FcmMarin::updateorCreate(
                // Condition pour trouver le marin: les relations fonctionnent sur les id et pas sur les uuid
                //
                ['rh_marin_id' => $marin->id],
                [
                    'data' => null,
                    'cohorte_id' => $cohorte->id,
                ],
            );

            $notification = UserGeneratedEventNotifications::getMess(UserGeneratedEventNotifications::COHORTE, $marin->nom);

            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send()
            ;
        } else {
            Log::warning('Aucun Marin trouvé  UUID :'.$marin->uuid);
        }
    }
}
