<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Services\CalculTauxParcoursService;

class RestoreElementMarinParcoursListener
{
    use LoadsModelsDynamically;

    public $comment;
    public $selectedItems;
    public $commentRestore;
    public $dateRestore;

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(EventTypeManager::RESTORE_ELMT_MARIN_PARCOURS)) {
            return;
        }

        // Récupérer le marin et les données
        $record = $event->record; // //$record: FcmCentral\Models\Marin
        $data = $event->data;
        $this->commentRestore = $event->data['selectedItems']['commentaire'];
        $this->selectedItems = $event->data['selectedItems'];
        $this->dateRestore = $event->data['selectedItems']['date_restore'];



        foreach ($record->parcoursSerialises as $parcours) {
            // Décoder le JSON du champ parcoursmarin
            $parcoursMarin = json_decode($parcours->pivot->parcoursmarin, true);

            // ÉTAPE 1 : Remettre tout à false
            $this->resetAllToFalse($parcoursMarin);



            // ÉTAPE 2 : Mettre à true seulement les éléments dans restore
            foreach ($this->selectedItems['restore'] as $type => $items) {
                $this->updateParcoursMarinItems($parcoursMarin, $type, $items,  $record->marin->displayName ?? $record->marin->nom . ' ' . $record->marin->prenom . ' ' . $record->marin->grade);
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

        $notification = Constants::getMess(Constants::RESTORE_ELMT_MARIN_PARCOURS, $record->marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }


    /**
     * Remettre tous les éléments du parcours à false
     */
    protected function resetAllToFalse(&$parcoursMarin)
    {
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            // Reset fonction
            $fonction['etat_valid'] = false;
            $fonction['commentaire'] = null;
            $fonction['date_validation'] = null;
            $fonction['valideur'] = null;
            $fonction['commentaire_proposition'] = null;
            $fonction['date_proposition'] = null;

            // Reset compétences
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    $competence['etat_valid'] = false;
                    $competence['commentaire'] = null;
                    $competence['date_validation'] = null;
                    $competence['valideur'] = null;
                    $competence['commentaire_proposition'] = null;
                    $competence['date_proposition'] = null;

                    // Reset savoir-faires
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            $savoirFaire['etat_valid'] = false;
                            $savoirFaire['commentaire'] = null;
                            $savoirFaire['date_validation'] = null;
                            $savoirFaire['valideur'] = null;
                            $savoirFaire['commentaire_proposition'] = null;
                            $savoirFaire['date_proposition'] = null;

                            // Reset activités
                            if (isset($savoirFaire['activites'])) {
                                foreach ($savoirFaire['activites'] as &$activite) {
                                    $activite['etat_valid'] = false;
                                    $activite['commentaire'] = null;
                                    $activite['date_validation'] = null;
                                    $activite['valideur'] = null;
                                    $activite['commentaire_proposition'] = null;
                                    $activite['date_proposition'] = null;
                                }
                            }
                        }
                    }
                }
            }

            // Reset autres types directement sous fonction
            foreach (['competences', 'savoirfaires', 'activites'] as $type) {
                if (isset($fonction[$type])) {
                    foreach ($fonction[$type] as &$element) {
                        $element['etat_valid'] = false;
                        $element['commentaire'] = null;
                        $element['date_validation'] = null;
                        $element['valideur'] = null;
                        $element['commentaire_proposition'] = null;
                        $element['date_proposition'] = null;

                    }
                }
            }
        }
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
                $fonction['commentaire'] =  $this->selectedItems['commentaire'];
                $fonction['date_validation'] = $this->selectedItems['date_restore'];
                $fonction['valideur'] = $displayName;
            }

            // --- TRAITEMENT DES AUTRES TYPES ---
            if (isset($fonction[$type])) {
                foreach ($fonction[$type] as &$element) {
                    if (isset($items[$element['id']])) {
                        $element['etat_valid'] = $items[$element['id']];
                        $element['commentaire'] = $this->selectedItems['commentaire'];
                        $element['date_validation'] = $this->selectedItems['date_restore'];
                        $element['valideur'] = $displayName;
                    }
                }
            }

            // --- SOUS-NIVEAUX ---
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    if ($type === 'competences' && isset($items[$competence['id']])) {
                        $competence['etat_valid'] = $items[$competence['id']];
                        $competence['commentaire'] = $this->selectedItems['commentaire'];
                        $competence['date_validation'] = $this->selectedItems['date_restore'];
                        $competence['valideur'] = $displayName;
                    }
                    if (isset($competence[$type])) {
                        foreach ($competence[$type] as &$element) {
                            if (isset($items[$element['id']])) {
                                $element['etat_valid'] = $items[$element['id']];
                                $element['commentaire'] =  $this->selectedItems['commentaire'];
                                $element['date_validation'] =  $this->selectedItems['date_restore'];
                                $element['valideur'] = $displayName;
                            }
                        }
                    }
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            if ($type === 'savoirfaires' && isset($items[$savoirFaire['id']])) {
                                $savoirFaire['etat_valid'] = $items[$savoirFaire['id']];
                                $savoirFaire['commentaire'] = $this->selectedItems['commentaire'];
                                $savoirFaire['date_validation'] = $this->selectedItems['date_restore'];
                                $savoirFaire['valideur'] = $displayName;
                            }
                            if (isset($savoirFaire[$type])) {
                                foreach ($savoirFaire[$type] as &$element) {
                                    if (isset($items[$element['id']])) {
                                        $element['etat_valid'] = $items[$element['id']];
                                        $element['commentaire'] = $this->selectedItems['commentaire'];
                                        $element['date_validation'] = $this->selectedItems['date_restore'];
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
