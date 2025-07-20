<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;

class AnnulProposElementMarinParcoursListener
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
        if (!$event->isOfType(EventTypeManager::ANNUL_PROPOS_ELMT_MARIN_PARCOURS)) {
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
            foreach ($this->selectedItems['aValiderPropos'] as $type => $items) {
                $this->annulPropositions($parcoursMarin, $type, $items, $record->marin->displayName ?? $record->marin->nom . ' ' . $record->marin->prenom . ' ' . $record->marin->grade);
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

        $notification = Constants::getMess(Constants::ANNUL_PROPOS_ELMT_MARIN_PARCOURS, $record->marin->nom);

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
    protected function annulPropositions(&$parcoursMarin, $type, $items)
{
    foreach ($parcoursMarin['fonctions'] as &$fonction) {
        // --- TRAITEMENT DIRECT DES FONCTIONS ---
        if ($type === 'fonctions' && isset($items[$fonction['id']])) {
            $fonction['commentaire_proposition'] = null;
            $fonction['date_proposition'] = null;
            $fonction['etat_propos'] = null;
        }

        // --- TRAITEMENT DES AUTRES TYPES ---
        if (isset($fonction[$type])) {
            foreach ($fonction[$type] as &$element) {
                // Ici, on vérifie directement si l'ID existe comme clé dans $items
                if (array_key_exists($element['id'], $items)) {
                    $element['commentaire_proposition'] = null;
                    $element['date_proposition'] = null;
                    $element['etat_propos'] = null;
                }
            }
        }

        // --- SOUS-NIVEAUX ---
        if (isset($fonction['competences'])) {
            foreach ($fonction['competences'] as &$competence) {
                if ($type === 'competences' && array_key_exists($competence['id'], $items)) {
                    $competence['commentaire_proposition'] = null;
                    $competence['date_proposition'] = null;
                    $competence['etat_propos'] = null;
                }
                if (isset($competence[$type])) {
                    foreach ($competence[$type] as &$element) {
                        if (array_key_exists($element['id'], $items)) {
                            $element['commentaire_proposition'] = null;
                            $element['date_proposition'] = null;
                            $element['etat_propos'] = null;
                        }
                    }
                }
                if (isset($competence['savoirfaires'])) {
                    foreach ($competence['savoirfaires'] as &$savoirFaire) {
                        if ($type === 'savoirfaires' && array_key_exists($savoirFaire['id'], $items)) {
                            $savoirFaire['commentaire_proposition'] = null;
                            $savoirFaire['date_proposition'] = null;
                            $savoirFaire['etat_propos'] = null;
                        }
                        if (isset($savoirFaire[$type])) {
                            foreach ($savoirFaire[$type] as &$element) {
                                if (array_key_exists($element['id'], $items)) {
                                    $element['commentaire_proposition'] = null;
                                    $element['date_proposition'] = null;
                                    $element['etat_propos'] = null;
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
