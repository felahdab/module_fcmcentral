<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Helpers\DataParcoursMarinHelper;
use Modules\FcmCommun\Services\CalculTauxParcoursService;

class RebootElementMarinParcoursListener
{
    use LoadsModelsDynamically;

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(EventTypeManager::REBOOT_ELMT_MARIN_PARCOURS)) {
            return;
        }

        // Récupérer le marin et les données
        $record = $event->record; // //$record: FcmCentral\Models\Marin
        $data = $event->data;
       

        // Récupérer le FCM Marin
        //$fcmMarin = $record->complements_fcm;
        $fcmMarin = $record;

        if (!$fcmMarin) {
            Log::warning('FCM Marin introuvable pour reboot.', ['rh_marin_id' => $record->marin->id]);
            return;
        }

        // Vérifier qu'un parcours sérialisé est attaché
        if ($fcmMarin->parcoursSerialises->count() === 0) {
            Log::warning('Aucun parcours sérialisé trouvé pour reboot.', ['fcmmarin_id' => $fcmMarin->id]);
            
            Notification::make()
                ->title('Erreur')
                ->body('Aucun parcours trouvé à rebooter')
                ->danger()
                ->send();
            return;
        }

        // Récupérer le parcours sérialisé actuel (on prend le premier s'il y en a plusieurs)
        $parcoursSerialise = $fcmMarin->parcoursSerialises->first();
    
       
        
        // Récupérer le parcours sérialisé original
        $parcoursSerialiseJson = $parcoursSerialise->parcours;
        
        // Regénérer le parcours marin avec les champs par défaut
        $parcoursMarinInfos = DataParcoursMarinHelper::rajoutChampSupplementaire($parcoursSerialiseJson, null, 'init');
        $parcoursMarin = json_encode($parcoursMarinInfos);

        /////////////////////////////
        // Solution 1 :  Mettre à jour le pivot avec le parcours réinitialisé
        /////////////////////////////

        $fcmMarin->parcoursSerialises()->updateExistingPivot($parcoursSerialise->id,[
            'parcoursmarin' => $parcoursMarin,
            'taux_global'=> 0,
            'taux_activite'=> 0,
            'taux_stage'=> 0,
            'updated_at' => now()
        ]);

        /////////////////////////////
        // Fin Solution 1 :  Mettre à jour le pivot avec le parcours réinitialisé
        /////////////////////////////

        /////////////////////////////
        // Solution 2 : Detach / Attach
        /////////////////////////////
        
        // Pour le serialise
        // $currentPivotData = $parcoursSerialise->pivot;
        // Preparer les nouvelles données pivot
        // $newPivotData = [
        //     'taux_global'       => $currentPivotData->taux_global ?? 0 ,
        //     'taux_stage'        => $currentPivotData->taux_stage ?? 0 ,
        //     'taux_activite'     => $currentPivotData->taux_activite ?? 0 ,
        //     'parcoursmarin'     => $parcoursMarin ,
        // ];
        // Detacher ancien
        // $fcmMarin->parcoursSerialises()->detach($parcoursSerialise->id);
        // Attacher Nouveau
        // $fcmMarin->parcoursSerialises()->attach($parcoursSerialise->id, $newPivotData);

        /////////////////////////////
        // Fin Solution 2 : Detach / Attach
        /////////////////////////////


        // Log de l'action
        Log::info('Parcours marin rebooté.', [
            'fcmmarin_id' => $fcmMarin->id,
            'parcoursserialise_id' => $parcoursSerialise->id,
            'commentaire' => $data['commentaire'] ?? 'Reboot automatique'
        ]);

        $notification = Constants::getMess(Constants::REBOOT_ELMT_MARIN_PARCOURS, $record->marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }

    


}
