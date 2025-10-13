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
use Modules\FcmCentral\Models\Objectif;

class CreationParcoursSerialiseListener
{
    use LoadsModelsDynamically;

    /**
     * Gere l'evenement.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        if (! $event->isOfType(UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE)){
            return;
        }
       
        try {
            $parcours = Parcours::where('uuid', $event->object_uuid)->first();
           
            if (!$parcours) {
                Log::error("Parcours non trouvé avec UUID: {$event->object_uuid}");
                return;
            }

            $data = $event->detail;
            $date = $data['date_de_debut'];
            $version = $data['version'];

            // ✅ CHARGER EXPLICITEMENT TOUTES LES RELATIONS NÉCESSAIRES
            $parcours->load([
                'annees' => function ($anneeQuery) {
                    $anneeQuery->with([
                        'savoirfaires' => function ($savoirfaireQuery) {
                            // Pas besoin de charger objectifs ici car on le fait manuellement
                        }
                    ])->orderBy('fcmcentral_annee_parcours.ordre');
                }
            ]);

            // ✅ DEBUG: Vérifier ce qui est chargé
            Log::info("Parcours {$parcours->libelle_court} - Nombre d'années: " . $parcours->annees->count());

            // ✅ SÉRIALISER LES DONNÉES AVEC TOUTES LES RELATIONS
            $serializedData = $this->buildCompleteParcoursData($parcours);
         
            // ✅ DEBUG: Compter le total d'objectifs sérialisés
            $totalObjectifs = $this->countObjectifsInSerializedData($serializedData);
            Log::info("Total objectifs sérialisés: {$totalObjectifs}");
           
            // Enregistrer dans ParcoursSerialize
            ParcoursSerialise::create([
                'parcours_id'   => $parcours->id,
                'parcours'      => $serializedData,
                'date_debut'    => $date,
                'libelle_court' => $parcours->libelle_court,
                'libelle_long'  => $parcours->libelle_long,
                'version'       => $version,
            ]);

            Log::info("Parcours {$parcours->libelle_court} sérialisé à la date {$date} avec {$totalObjectifs} objectifs");

            $notification = Constants::getMess(Constants::PARCOURS, $parcours->libelle_court);
           
            // Notification Filament
            Notification::make()
                ->title($notification['title'])
                ->success()
                ->body($notification['message'])
                ->send();

        } catch (\Exception $e) {
            Log::error("Erreur lors de la sérialisation du parcours", [
                'uuid' => $event->object_uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
           
            Notification::make()
                ->title('Erreur de sérialisation')
                ->danger()
                ->body('Impossible de sérialiser le parcours: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * ✅ CONSTRUIRE MANUELLEMENT LA STRUCTURE COMPLÈTE
     */
    private function buildCompleteParcoursData(Parcours $parcours): array
{
    $data = $parcours->toArray();
   
    // ✅ Construire manuellement les années avec leurs relations
    $data['annees'] = $parcours->annees->map(function ($annee) {
        $anneeData = $annee->toArray();
       
        Log::info("Traitement année {$annee->code} - Nombre de savoir-faires: " . $annee->savoirfaires->count());
       
        // ✅ Construire les savoir-faires avec leurs objectifs ET leur ordre
        $anneeData['savoirfaires'] = $annee->savoirfaires
            ->sortBy(function ($savoirfaire) {
                // ✅ TRIER PAR L'ORDRE DU PIVOT
                return $savoirfaire->pivot->ordre ?? 0;
            })
            ->map(function ($savoirfaire) use ($annee) {
                $savoirfaireData = $savoirfaire->toArray();
               
                // ✅ Vérifier que le pivot existe
                if (!$savoirfaire->pivot || !$savoirfaire->pivot->id) {
                    Log::warning("Pivot manquant pour savoir-faire {$savoirfaire->code} dans année {$annee->code}");
                    $savoirfaireData['objectifs'] = [];
                    return $savoirfaireData;
                }
               
                // ✅ AJOUTER L'ORDRE DU PIVOT DANS LES DONNÉES SÉRIALISÉES
                $pivotId = $savoirfaire->pivot->id;
                $savoirfaireData['pivot']['ordre'] = $savoirfaire->pivot->ordre ?? 0;
               
                // ✅ RÉCUPÉRER LES OBJECTIFS VIA LE PIVOT
                $objectifs = Objectif::where('annee_savoirfaire_id', $pivotId)
                    ->orderBy('ordre')
                    ->get()
                    ->toArray();
               
                $savoirfaireData['objectifs'] = $objectifs;
               
                Log::info("SF {$savoirfaire->code} (pivot {$pivotId}, ordre {$savoirfaire->pivot->ordre}): " . count($objectifs) . " objectifs trouvés");
               
                return $savoirfaireData;
            })
            ->values() // ✅ Réindexer le tableau après le tri
            ->toArray();
       
        return $anneeData;
    })->toArray();
   
    return $data;
}


    /**
     * ✅ COMPTER LES OBJECTIFS DANS LES DONNÉES SÉRIALISÉES
     */
    private function countObjectifsInSerializedData(array $data): int
    {
        $count = 0;
       
        if (isset($data['annees'])) {
            foreach ($data['annees'] as $annee) {
                if (isset($annee['savoirfaires'])) {
                    foreach ($annee['savoirfaires'] as $savoirfaire) {
                        if (isset($savoirfaire['objectifs'])) {
                            $count += count($savoirfaire['objectifs']);
                        }
                    }
                }
            }
        }
       
        return $count;
    }
}
