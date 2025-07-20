<?php

namespace Modules\FcmCentral\Listeners\Save;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;

class CreationParcoursSerialiseListener
{

    use LoadsModelsDynamically;
    /**
     * Gere l'evenement.
     * 
     * @param UserGeneratedEvent $event;
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE)){
            return;
        }
        
        $parcours       = Parcours::where('uuid', $event->object_uuid)->first();
        $data           = $event->detail;
       
        $date           = $data['date_de_debut'];
        $version        = $data['version'];
    
        // Sérialiser les données du parcours et ses relations
        $serializedData =  $parcours->toArray();
        
        // Enregistrer dans ParcoursSerialize
        ParcoursSerialise::create([
            'parcours_id'   => $parcours->id,
            'parcours'      => $serializedData,
            'date_debut'    => $date,
            'libelle_court' => $parcours->libelle_court,
            'libelle_long'  => $parcours->libelle_long,
            'version'       => $version,
        ]);

        Log::info("Parcours {$parcours->libelle_court} sérialisé à la date {$date}");

        $notification = Constants::getMess(Constants::PARCOURS,$parcours->libelle_court);
        
        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
        
    }
}

