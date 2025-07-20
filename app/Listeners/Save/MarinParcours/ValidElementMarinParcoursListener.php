<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;

use App\Models\User;

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
        $valideur = User::where('uuid', $event->user_uuid)->first();
        $type_element = $data['type_element'];
        $uuid_element = $data['uuid_element'];
        $date = $data['date'];

        $donnees_du_parcours = $marin->complements_fcm->donnees_du_parcours;
        //ddd($donnees_du_parcours);
        $parcoursMarin = $donnees_du_parcours->parcoursmarin;
        
        //ddd($parcoursMarin);
        // Mise a jour en fonction de selectedItems
        
        $this->updateParcoursMarinItems($parcoursMarin, $type_element,  $uuid_element, $valideur->displayName ?? $valideur->nom . ' ' . $valideur->prenom);
        ddd($parcoursMarin);

        // Recalcul des taux
        $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
        $donnees_du_parcours->refresh();
        ddd($donnees_du_parcours);
        $donnees_du_parcours->taux_global= $parcoursMarin['taux_global'];
        $donnees_du_parcours->pivot->taux_stage= $parcoursMarin['taux_stage'];
        $donnees_du_parcours->pivot->taux_activite= $parcoursMarin['taux_activite'];
        

        // Sauvegarder les données d'origine avec les modifications dans selectedItems
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $parcours->pivot->save();

        

        // Notification de succès
        // Notification::make()
        // ->title('Données sauvegardées avec succès')
        // ->success()
        // ->send();

        $notification = Constants::getMess(Constants::VALID_ELMT_MARIN_PARCOURS, $marin->nom);

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
    protected function updateParcoursMarinItems(&$parcoursMarin, $type, $item, $displayName)
    {
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            // --- TRAITEMENT DIRECT DES FONCTIONS ---
            if ($type === 'fonctions' && $fonction['uuid'] == $item) {
                $fonction['etat_valid'] = true;
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
                    if ($type === 'competences' && $competence['uuid'] == $item) {
                        $competence['etat_valid'] = true;
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
                            if ($type === 'savoirfaires' && $savoirFaire['uuid'] == $item) {
                                $savoirFaire['etat_valid'] = true;
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
