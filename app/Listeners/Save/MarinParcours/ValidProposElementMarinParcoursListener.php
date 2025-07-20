<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\Helpers\HierarchyParentsVerifierHelper;
use Modules\FcmCommun\Services\CalculTauxParcoursService;

class ValidProposElementMarinParcoursListener
{
    use LoadsModelsDynamically;

    public $comment;
    public $selectedItems;
    public $hierarchyMap;

    /**
     * Gère l'événement.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(EventTypeManager::VALID_PROPOS_ELMT_MARIN_PARCOURS)) {
            return;
        }

        // Récupérer le marin et les données
        $record = $event->record;
        $data = $event->data;
        $this->comment = $event->data['commentaire'];
        $this->selectedItems = $event->data['selectedItems'];
        $this->hierarchyMap = $this->selectedItems['map'] ?? [];

        $displayName = $record->marin->displayName ?? $record->marin->nom . ' ' . $record->marin->prenom . ' ' . $record->marin->grade;

        foreach ($record->parcoursSerialises as $parcours) {
            // Décoder le JSON du champ parcoursmarin
            $parcoursMarin = json_decode($parcours->pivot->parcoursmarin, true);

            // 1. Valider les propositions
            foreach ($this->selectedItems['aValiderPropos'] as $type => $items) {
                $this->validPropositions($parcoursMarin, $type, $items, $displayName);
            }

            // 2. Construire la structure selectedItems pour la vérification des parents
            $tempSelectedItems = $this->buildSelectedItemsFromParcours($parcoursMarin);
            
            // 3. Utiliser le Helper pour vérifier les parents
            if (!empty($this->hierarchyMap)) {
                $verifier = new HierarchyParentsVerifierHelper($tempSelectedItems, $this->hierarchyMap);
                $updatedSelectedItems = $verifier->verifyAll('validation');
                
                // 4. Appliquer les changements aux parents dans le parcours
                $this->applyParentValidationToParcours($parcoursMarin, $updatedSelectedItems, $displayName);
            }

            // Recalcul des taux
            $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
            $parcours->pivot->taux_global= $parcoursMarin['taux_global'];
            $parcours->pivot->taux_stage= $parcoursMarin['taux_stage'];
            $parcours->pivot->taux_activite= $parcoursMarin['taux_activite'];

            // Sauvegarder les données
            $parcours->pivot->parcoursmarin = json_encode($parcoursMarin);
            $parcours->pivot->save();
        }

        $notification = Constants::getMess(Constants::VALID_PROPOS_ELMT_MARIN_PARCOURS, $record->marin->nom);

        // Notification Filament
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }

    /**
     * Valider les propositions dans le parcours
     */
    protected function validPropositions(&$parcoursMarin, $type, $items, $displayName)
    {
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            // --- TRAITEMENT DIRECT DES FONCTIONS ---
            if ($type === 'fonctions' && array_key_exists($fonction['id'], $items)) {
                $this->validateElement($fonction, $items[$fonction['id']], $displayName);
            }

            // --- TRAITEMENT DES AUTRES TYPES ---
            if (isset($fonction[$type])) {
                foreach ($fonction[$type] as &$element) {
                    if (array_key_exists($element['id'], $items)) {
                        $this->validateElement($element, $items[$element['id']], $displayName);
                    }
                }
            }

            // --- SOUS-NIVEAUX ---
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    if ($type === 'competences' && array_key_exists($competence['id'], $items)) {
                        $this->validateElement($competence, $items[$competence['id']], $displayName);
                    }
                    
                    if (isset($competence[$type])) {
                        foreach ($competence[$type] as &$element) {
                            if (array_key_exists($element['id'], $items)) {
                                $this->validateElement($element, $items[$element['id']], $displayName);
                            }
                        }
                    }
                    
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            if ($type === 'savoirfaires' && array_key_exists($savoirFaire['id'], $items)) {
                                $this->validateElement($savoirFaire, $items[$savoirFaire['id']], $displayName);
                            }
                            
                            if (isset($savoirFaire[$type])) {
                                foreach ($savoirFaire[$type] as &$element) {
                                    if (array_key_exists($element['id'], $items)) {
                                        $this->validateElement($element, $items[$element['id']], $displayName);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Valider un élément (proposition acceptée)
     */
    protected function validateElement(&$element, $itemData, $displayName)
    {
        $element['etat_valid'] = true;
        $element['commentaire'] = $itemData['commentaire_proposition'] ?? $this->comment;
        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
        
        // Effacer les données de proposition
        $element['commentaire_proposition'] = null;
        $element['date_proposition'] = null;
        $element['etat_propos'] = null;
    }

    /**
     * Construire selectedItems à partir du parcours actuel
     */
    protected function buildSelectedItemsFromParcours($parcoursMarin): array
    {
        $selectedItems = [
            'gestion' => [],
            'init' => $this->selectedItems['init'] ?? [],
            'sauve' => []
        ];
        
        foreach ($parcoursMarin['fonctions'] as $fonction) {
            if ($fonction['etat_valid']) {
                $selectedItems['gestion']['fonctions'][$fonction['id']] = true;
                
                // Ajouter dans sauve si pas dans init
                if (!isset($selectedItems['init']['fonctions'][$fonction['id']])) {
                    $selectedItems['sauve']['fonctions'][$fonction['id']] = true;
                }
            }
            
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as $competence) {
                    if ($competence['etat_valid']) {
                        $selectedItems['gestion']['competences'][$competence['id']] = true;
                        
                        // Ajouter dans sauve si pas dans init
                        if (!isset($selectedItems['init']['competences'][$competence['id']])) {
                            $selectedItems['sauve']['competences'][$competence['id']] = true;
                        }
                    }
                    
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as $savoirfaire) {
                            if ($savoirfaire['etat_valid']) {
                                $selectedItems['gestion']['savoirfaires'][$savoirfaire['id']] = true;
                                
                                // Ajouter dans sauve si pas dans init
                                if (!isset($selectedItems['init']['savoirfaires'][$savoirfaire['id']])) {
                                    $selectedItems['sauve']['savoirfaires'][$savoirfaire['id']] = true;
                                }
                            }
                            
                            if (isset($savoirfaire['activites'])) {
                                foreach ($savoirfaire['activites'] as $activite) {
                                    if ($activite['etat_valid']) {
                                        $selectedItems['gestion']['activites'][$activite['id']] = true;
                                        
                                        // Ajouter dans sauve si pas dans init
                                        if (!isset($selectedItems['init']['activites'][$activite['id']])) {
                                            $selectedItems['sauve']['activites'][$activite['id']] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $selectedItems;
    }

    /**
     * Appliquer la validation des parents dans le parcours
     */
    protected function applyParentValidationToParcours(&$parcoursMarin, $updatedSelectedItems, $displayName)
    {
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            // Vérifier si la fonction doit être validée
            if (isset($updatedSelectedItems['gestion']['fonctions'][$fonction['id']]) && !$fonction['etat_valid']) {
                $fonction['etat_valid'] = true;
                $fonction['commentaire'] = $this->comment ?: 'Validation automatique suite à validation des enfants';
                $fonction['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                $fonction['valideur'] = $displayName;
                
                // Nettoyer les données de proposition si elles existent
                $fonction['commentaire_proposition'] = null;
                $fonction['date_proposition'] = null;
                $fonction['etat_propos'] = null;
            }
            
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    // Vérifier si la compétence doit être validée
                    if (isset($updatedSelectedItems['gestion']['competences'][$competence['id']]) && !$competence['etat_valid']) {
                        $competence['etat_valid'] = true;
                        $competence['commentaire'] = $this->comment ?: 'Validation automatique suite à validation des enfants';
                        $competence['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                        $competence['valideur'] = $displayName;
                        
                        // Nettoyer les données de proposition
                        $competence['commentaire_proposition'] = null;
                        $competence['date_proposition'] = null;
                        $competence['etat_propos'] = null;
                    }
                    
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirfaire) {
                            // Vérifier si le savoir-faire doit être validé
                            if (isset($updatedSelectedItems['gestion']['savoirfaires'][$savoirfaire['id']]) && !$savoirfaire['etat_valid']) {
                                $savoirfaire['etat_valid'] = true;
                                $savoirfaire['commentaire'] = $this->comment ?: 'Validation automatique suite à validation des enfants';
                                $savoirfaire['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                                $savoirfaire['valideur'] = $displayName;
                                
                                // Nettoyer les données de proposition
                                $savoirfaire['commentaire_proposition'] = null;
                                $savoirfaire['date_proposition'] = null;
                                $savoirfaire['etat_propos'] = null;
                            }
                        }
                    }
                }
            }
        }
    }
}
