<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Helpers\DataParcoursMarinHelper;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;

class ParcoursSerialiseMarinListener
{

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN)) {
            return;
        }

        // Récupérer le marin et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();
        $data = $event->detail;

        // Récupérer le parcours serialisé
        $parcours = Parcours::where("uuid", $data["parcours"])->first();
        $lastVersion = $parcours->getLatestSerializedVersion();
        if ($lastVersion == 0){
            logger()->warning("Le parcours attribué au marin n'a pas de version sérialisée");
            Notification::make()
                ->title("Parcours sans version active")
                ->warning()
                ->body("Le parcours sélectionné n'a aucune version figée.")
                ->send();
            return;
        }

        // On sélectionne donc la dernière version du parcours sérialisé pour l'attribuer effectivement au marin.
        $parcoursSerialise = ParcoursSerialise::where("parcours_id", $parcours->id)
            ->where("version", $lastVersion)
            ->first();

        // Extrait du modèle FcmCentral\Models\Marin:
        // * Grace à la définition de cet attribut, le developpeur peut utiliser les méthodes suivantes:
        // * $marin->complements_fcm->cohorte
        // * $marin->complements_fcm->mentor
        // * $marin->complements_fcm->donnes_du_parcours->taux_global
        // * $marin->complements_fcm->donnes_du_parcours->taux_stage
        // * $marin->complements_fcm->donnes_du_parcours->taux_activite
        // * $marin->complements_fcm->donnes_du_parcours->parcours_serialise
        // Si le record provient de Rh_Marins
        
        $fcmMarin = $marin->complements_fcm;

        // Recuperation Parcours Serialise pour le sauvegarder dans le champ parcoursmarin avec champ supplementaire
        
        $parcoursSerialiseJson = $parcoursSerialise->parcours;
        $parcoursMarinInfos = DataParcoursMarinHelper::rajoutChampSupplementaire($parcoursSerialiseJson, null, 'init');


        // Récupérer les taux et autres données
        $tauxGlobal = $data['taux_global'] ?? 0;
        $tauxStage = $data['taux_stage'] ?? 0;
        $tauxActivite = $data['taux_activite'] ?? 0;
        $parcoursMarin = json_encode($parcoursMarinInfos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Si un parcours sérialisé est déjà attaché, le détacher
        if ($fcmMarin->parcoursSerialises->count())
        {
            $fcmMarin->parcoursSerialises()->detach();
        }

        // Attacher le parcours serialisé au marin
        $fcmMarin->parcoursSerialises()->attach($parcoursSerialise->id, [
            'taux_global' => $tauxGlobal,
            'taux_stage' => $tauxStage,
            'taux_activite' => $tauxActivite,
            'parcoursmarin' => $parcoursMarin,
        ]);

        // Log de l'action
        Log::info('Parcours serialisé attribué au marin.', [
            'fcmmarin_id' => $fcmMarin->id,
            'parcoursserialise_id' => $parcoursSerialise->id,
        ]);

        $notification = Constants::getMess(Constants::MARIN_PARCOURS, $marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }

}
