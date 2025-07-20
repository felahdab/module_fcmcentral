<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;

use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Services\CalculTauxParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;

class ValidElementMarinParcoursListener
{
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
        if (!$event->isOfType(UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS)) {
            return;
        }

        // Récupérer le marin et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();

        $data = $event->detail;
        $this->comment = $data['commentaire'];
        $valideur = Marin::where('uuid', $data["validateur"]);
        $type_element = $data['type_element'];
        $uuid_element = $data['uuid_element'];
        $date = $data['date'];

        $this->selectedItems = $event->data['selectedItems'];


        foreach ($record->parcoursSerialises as $parcours) {
            // Décoder le JSON du champ parcoursmarin
            $parcoursMarin = json_decode($parcours->pivot->parcoursmarin, true);


            // Mise a jour en fonction de selectedItems
            foreach ($this->selectedItems['sauve'] as $type => $items) {
                $this->updateParcoursMarinItems($parcoursMarin, $type, $items, $record->marin->displayName ?? $record->marin->nom . ' ' . $record->marin->prenom . ' ' . $record->marin->grade);
            }

            // Recalcul des taux
            $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
            $parcours->pivot->taux_global= $parcoursMarin['taux_global'];
            $parcours->pivot->taux_stage= $parcoursMarin['taux_stage'];
            $parcours->pivot->taux_activite= $parcoursMarin['taux_activite'];
            

            // Sauvegarder les données d'origine avec les modifications dans selectedItems
            $parcours->pivot->parcoursmarin = json_encode($parcoursMarin);
            $parcours->pivot->save();

        }

        // Notification de succès
        // Notification::make()
        // ->title('Données sauvegardées avec succès')
        // ->success()
        // ->send();

        $notification = Constants::getMess(Constants::VALID_ELMT_MARIN_PARCOURS, $record->marin->nom);

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
                $fonction['etat_valid'] = $items[$fonction['id']];
                $fonction['commentaire'] = $this->comment;
                $fonction['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                $fonction['valideur'] = $displayName;
            }

            // --- TRAITEMENT DES AUTRES TYPES ---
            if (isset($fonction[$type])) {
                foreach ($fonction[$type] as &$element) {
                    if (isset($items[$element['id']])) {
                        $element['etat_valid'] = $items[$element['id']];
                        $element['commentaire'] = $this->comment;
                        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                        $element['valideur'] = $displayName;
                    }
                }
            }

            // --- SOUS-NIVEAUX ---
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    if ($type === 'competences' && isset($items[$competence['id']])) {
                        $competence['etat_valid'] = $items[$competence['id']];
                        $competence['commentaire'] = $this->comment;
                        $competence['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                        $competence['valideur'] = $displayName;
                    }
                    if (isset($competence[$type])) {
                        foreach ($competence[$type] as &$element) {
                            if (isset($items[$element['id']])) {
                                $element['etat_valid'] = $items[$element['id']];
                                $element['commentaire'] = $this->comment;
                                $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                                $element['valideur'] = $displayName;
                            }
                        }
                    }
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            if ($type === 'savoirfaires' && isset($items[$savoirFaire['id']])) {
                                $savoirFaire['etat_valid'] = $items[$savoirFaire['id']];
                                $savoirFaire['commentaire'] = $this->comment;
                                $savoirFaire['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                                $savoirFaire['valideur'] = $displayName;
                            }
                            if (isset($savoirFaire[$type])) {
                                foreach ($savoirFaire[$type] as &$element) {
                                    if (isset($items[$element['id']])) {
                                        $element['etat_valid'] = $items[$element['id']];
                                        $element['commentaire'] = $this->comment;
                                        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                                        $element['valideur'] = $displayName;
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
