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
use Modules\FcmCommun\Services\ReCalculEtatParcoursService;

class ValidProposElementMarinParcoursListener
{
    use LoadsModelsDynamically;

    public $comment;
    public $selectedItems;
    public $hierarchyMap;

    /**
     * Gère l'événement de validation de propositions.
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(UserGeneratedEventTypes::VALID_PROPOS_ELMT_MARIN_PARCOURS)) {
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

            Log::info("=== VALIDATION DE PROPOSITIONS ===");
            Log::info("Marin: {$record->marin->nom}");
            Log::info("Items à valider:", $this->selectedItems['aValiderPropos'] ?? []);

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

            // ✅ RECALCULER LES ÉTATS DE VALIDATION HIÉRARCHIQUES
            ReCalculEtatParcoursService::recalculerEtats($parcoursMarin);

            // Recalcul des taux
            $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
            $parcours->pivot->taux_global = $parcoursMarin['taux_global'];
            $parcours->pivot->taux_stage = $parcoursMarin['taux_stage'];
            $parcours->pivot->taux_activite = $parcoursMarin['taux_activite'];

            // Sauvegarder les données
            $parcours->pivot->parcoursmarin = json_encode($parcoursMarin);
            $parcours->pivot->save();

            Log::info("✅ Parcours sauvegardé avec succès");
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
     * ✅ VALIDER LES PROPOSITIONS DANS LE PARCOURS (nouvelle structure)
     */
    protected function validPropositions(&$parcoursMarin, $type, $items, $displayName)
    {
        Log::info("Validation propositions type: {$type}");
        
        foreach ($parcoursMarin['annees'] as &$annee) {
            // --- TRAITEMENT DIRECT DES ANNÉES ---
            if ($type === 'annees' && array_key_exists($annee['id'], $items)) {
                $this->validateElement($annee, $items[$annee['id']], $displayName);
                Log::info("✅ Année validée: {$annee['uuid']}");
            }

            // --- TRAITEMENT DES SAVOIR-FAIRES ---
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirFaire) {
                    // ✅ Utiliser l'ID du pivot pour identifier le savoir-faire
                    $pivotId = $savoirFaire['pivot']['id'] ?? null;
                    
                    if ($type === 'savoirfaires' && $pivotId && array_key_exists($pivotId, $items)) {
                        $this->validateElement($savoirFaire, $items[$pivotId], $displayName);
                        Log::info("✅ Savoir-faire validé: {$savoirFaire['uuid']} (pivot: {$pivotId})");
                    }

                    // --- TRAITEMENT DES OBJECTIFS ---
                    if (isset($savoirFaire['objectifs']) && is_array($savoirFaire['objectifs'])) {
                        foreach ($savoirFaire['objectifs'] as &$objectif) {
                            if ($type === 'objectifs' && array_key_exists($objectif['id'], $items)) {
                                $this->validateElement($objectif, $items[$objectif['id']], $displayName);
                                
                                $objUuid = $objectif['uuid'] ?? "obj_{$pivotId}_{$objectif['id']}";
                                Log::info("✅ Objectif validé: {$objUuid}");
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * ✅ VALIDER UN ÉLÉMENT (proposition acceptée)
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
        
        Log::info("Élément validé avec succès");
    }

    /**
     * ✅ CONSTRUIRE selectedItems À PARTIR DU PARCOURS ACTUEL (nouvelle structure)
     */
    protected function buildSelectedItemsFromParcours($parcoursMarin): array
    {
        $selectedItems = [
            'gestion' => [],
            'init' => $this->selectedItems['init'] ?? [],
            'sauve' => []
        ];
        
        foreach ($parcoursMarin['annees'] as $annee) {
            // ✅ TRAITEMENT DES ANNÉES
            if ($annee['etat_valid']) {
                $selectedItems['gestion']['annees'][$annee['id']] = true;
                
                // Ajouter dans sauve si pas dans init
                if (!isset($selectedItems['init']['annees'][$annee['id']])) {
                    $selectedItems['sauve']['annees'][$annee['id']] = true;
                }
            }
            
            // ✅ TRAITEMENT DES SAVOIR-FAIRES
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as $savoirfaire) {
                    $pivotId = $savoirfaire['pivot']['id'] ?? null;
                    
                    if ($pivotId && $savoirfaire['etat_valid']) {
                        $selectedItems['gestion']['savoirfaires'][$pivotId] = true;
                        
                        // Ajouter dans sauve si pas dans init
                        if (!isset($selectedItems['init']['savoirfaires'][$pivotId])) {
                            $selectedItems['sauve']['savoirfaires'][$pivotId] = true;
                        }
                    }
                    
                    // ✅ TRAITEMENT DES OBJECTIFS
                    if (isset($savoirfaire['objectifs']) && is_array($savoirfaire['objectifs'])) {
                        foreach ($savoirfaire['objectifs'] as $objectif) {
                            if ($objectif['etat_valid']) {
                                $selectedItems['gestion']['objectifs'][$objectif['id']] = true;
                                
                                // Ajouter dans sauve si pas dans init
                                if (!isset($selectedItems['init']['objectifs'][$objectif['id']])) {
                                    $selectedItems['sauve']['objectifs'][$objectif['id']] = true;
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
     * ✅ APPLIQUER LA VALIDATION DES PARENTS DANS LE PARCOURS (nouvelle structure)
     */
    protected function applyParentValidationToParcours(&$parcoursMarin, $updatedSelectedItems, $displayName)
    {
        foreach ($parcoursMarin['annees'] as &$annee) {
            // ✅ Vérifier si l'année doit être validée
            if (isset($updatedSelectedItems['gestion']['annees'][$annee['id']]) && !$annee['etat_valid']) {
                $annee['etat_valid'] = true;
                $annee['commentaire'] = $this->comment ?: 'Validation automatique suite à validation des enfants';
                $annee['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                $annee['valideur'] = $displayName;
                
                // Nettoyer les données de proposition si elles existent
                $annee['commentaire_proposition'] = null;
                $annee['date_proposition'] = null;
                $annee['etat_propos'] = null;
                
                Log::info("✅ Année auto-validée: {$annee['uuid']}");
            }
            
            // ✅ TRAITEMENT DES SAVOIR-FAIRES
            if (isset($annee['savoirfaires'])) {
                foreach ($annee['savoirfaires'] as &$savoirfaire) {
                    $pivotId = $savoirfaire['pivot']['id'] ?? null;
                    
                    // Vérifier si le savoir-faire doit être validé
                    if ($pivotId && isset($updatedSelectedItems['gestion']['savoirfaires'][$pivotId]) && !$savoirfaire['etat_valid']) {
                        $savoirfaire['etat_valid'] = true;
                        $savoirfaire['commentaire'] = $this->comment ?: 'Validation automatique suite à validation des enfants';
                        $savoirfaire['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
                        $savoirfaire['valideur'] = $displayName;
                        
                        // Nettoyer les données de proposition
                        $savoirfaire['commentaire_proposition'] = null;
                        $savoirfaire['date_proposition'] = null;
                        $savoirfaire['etat_propos'] = null;
                        
                        Log::info("✅ Savoir-faire auto-validé: {$savoirfaire['uuid']} (pivot: {$pivotId})");
                    }
                }
            }
        }
    }
}
