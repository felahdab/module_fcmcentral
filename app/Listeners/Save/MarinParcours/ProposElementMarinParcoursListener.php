<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;

class ProposElementMarinParcoursListener
{
    use LoadsModelsDynamically;

    public $comment;
    public $selectedItems;

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(EventTypeManager::PROPOS_ELMT_MARIN_PARCOURS)) {
            return;
        }

        // Récupérer le marin et les données
        $record = $event->record; // //$record: FcmCentral\Models\Marin
        $data = $event->data;
        $this->comment = $event->data['commentaire'];
        $this->selectedItems = $event->data['selectedItems'];


        foreach ($record->parcoursSerialises as $parcours) {
            // Décoder le JSON du champ parcoursmarin
            $parcoursMarin = json_decode($parcours->pivot->parcoursmarin, true);


            // Mise a jour en fonction de selectedItems
            foreach ($this->selectedItems['propos'] as $type => $items) {
                $this->updateParcoursMarinItems($parcoursMarin, $type, $items, $record->marin->displayName ?? $record->marin->nom . ' ' . $record->marin->prenom . ' ' . $record->marin->grade);
            }


            // Sauvegarder les données d'origine avec les modifications dans selectedItems
            $parcours->pivot->parcoursmarin = json_encode($parcoursMarin);
            $parcours->pivot->save();
        }

        // Notification de succès
        // Notification::make()
        // ->title('Données sauvegardées avec succès')
        // ->success()
        // ->send();

        $notification = Constants::getMess(Constants::PROPOS_ELMT_MARIN_PARCOURS, $record->marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }






    /**
     * Mettre à jour les éléments imbriqués dans la structure.
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type, $items, $displayName)
{
    foreach ($parcoursMarin['fonctions'] as &$fonction) {
        // --- TRAITEMENT DIRECT DES FONCTIONS ---
        if ($type === 'fonctions' && isset($items[$fonction['id']])) {
            $fonction['etat_valid'] = false;
            $fonction['commentaire_proposition'] = $this->comment;
            $fonction['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
            
        }

        // --- TRAITEMENT DES AUTRES TYPES ---
        if (isset($fonction[$type])) {
            foreach ($fonction[$type] as &$element) {
                if (isset($items[$element['id']])) {
                    $element['etat_valid'] = false;
                    $element['commentaire_proposition'] = $this->comment;
                    $element['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
                   /// $element['valideur'] = $displayName;
                }
            }
        }

        // --- SOUS-NIVEAUX ---
        if (isset($fonction['competences'])) {
            foreach ($fonction['competences'] as &$competence) {
                if ($type === 'competences' && isset($items[$competence['id']])) {
                    $competence['etat_valid'] = false;
                    $competence['commentaire_proposition'] = $this->comment;
                    $competence['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
                    //$competence['valideur'] = $displayName;
                }
                if (isset($competence[$type])) {
                    foreach ($competence[$type] as &$element) {
                        if (isset($items[$element['id']])) {
                            $element['etat_valid'] = false;
                            $element['commentaire_proposition'] = $this->comment;
                            $element['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
                           // $element['valideur'] = $displayName;
                        }
                    }
                }
                if (isset($competence['savoirfaires'])) {
                    foreach ($competence['savoirfaires'] as &$savoirFaire) {
                        if ($type === 'savoirfaires' && isset($items[$savoirFaire['id']])) {
                            $savoirFaire['etat_valid'] = false;
                            $savoirFaire['commentaire_proposition'] = $this->comment;
                            $savoirFaire['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
                            //$savoirFaire['valideur'] = $displayName;
                        }
                        if (isset($savoirFaire[$type])) {
                            foreach ($savoirFaire[$type] as &$element) {
                                if (isset($items[$element['id']])) {
                                    $element['etat_valid'] = false;
                                    $element['commentaire_proposition'] = $this->comment;
                                    $element['date_proposition'] = \Carbon\Carbon::now()->toDateTimeString();
                                    //$element['valideur'] = $displayName;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

}
