<?php

namespace Modules\FcmCentral\Services;

class SelectionManagerService
{
    
    
    protected $selectedItems = [];
    protected $map = [];
 
    public function __construct()
    {
        $this->selectedItems = [
            'init' => [],      // Éléments initiaux (ne changent jamais)
            'gestion' => [],   // Éléments visibles (init + nouveaux)
            'sauve' => [],     // Seulement les nouveaux éléments (sans init)
        ];
    }
 
    /**
     * Initialise les sélections à partir des données du parcours
     */
    public function initializeSelections(array $parcours): array
    {
        $this->selectedItems = [
            'init' => [],
            'gestion' => [],
            'sauve' => [],
        ];
 
        foreach ($parcours as $parcoursItem) {
            $this->processParcoursForInitialization($parcoursItem['parcours']['fonctions']);
        }
 
        return $this->selectedItems;
    }
 
    /**
     * Traite les fonctions du parcours pour l'initialisation
     */
    protected function processParcoursForInitialization(array $fonctions): void
    {
        foreach ($fonctions as $fonction) {
            if (!empty($fonction['etat_valid'])) {
                $this->selectedItems['init']['fonctions'][$fonction['id']] = true;
                $this->selectedItems['gestion']['fonctions'][$fonction['id']] = true;
            }
            
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as $competence) {
                    if (!empty($competence['etat_valid'])) {
                        $this->selectedItems['init']['competences'][$competence['id']] = true;
                        $this->selectedItems['gestion']['competences'][$competence['id']] = true;
                    }
                    
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as $savoirfaire) {
                            if (!empty($savoirfaire['etat_valid'])) {
                                $this->selectedItems['init']['savoirfaires'][$savoirfaire['id']] = true;
                                $this->selectedItems['gestion']['savoirfaires'][$savoirfaire['id']] = true;
                            }
                            
                            if (isset($savoirfaire['activites'])) {
                                foreach ($savoirfaire['activites'] as $activite) {
                                    if (!empty($activite['etat_valid'])) {
                                        $this->selectedItems['init']['activites'][$activite['id']] = true;
                                        $this->selectedItems['gestion']['activites'][$activite['id']] = true;
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
     * Toggle un élément avec la logique de gestion des états
     */
    public function toggleCheck(string $type, int $id, array &$map): array
    {
        $this->map = &$map;
        
        $isChecked = isset($this->selectedItems['gestion'][$type][$id]);
        $checked = !$isChecked;
 
        // Met à jour l'état
        $this->setCheckedState($type, $id, $checked);
    
        // Propage la sélection aux enfants
        $this->selectDescendants($type, $id, $checked);
        
        // Mise à jour des parents
        $this->selectAscendants($type, $id);
 
        return $this->selectedItems;
    }
 
    /**
     * Définit l'état coché d'un élément selon les règles de gestion
     */
    protected function setCheckedState(string $type, int $id, bool $checked): void
    {
        if ($checked) {
            // Cocher l'élément
            $this->selectedItems['gestion'][$type][$id] = true;
            
            // Ajouter à sauve seulement si ce n'est pas dans init
            if (!isset($this->selectedItems['init'][$type][$id])) {
                $this->selectedItems['sauve'][$type][$id] = true;
            }
            
            // Met à jour l'état dans la map
            $this->map['descendant'][$type][$id]['etat_valid'] = true;
            $this->map['remontant'][$type][$id]['etat_valid'] = true;
        } else {
            // Décocher l'élément
            if (isset($this->selectedItems['init'][$type][$id])) {
                // Si l'élément est dans init, on ne peut pas le supprimer de gestion
                // On le garde dans gestion mais on peut le retirer de sauve si besoin
                // Dans ce cas, l'élément reste visuellement coché mais n'est pas sauvegardé comme modification
            } else {
                // Si ce n'est pas dans init, on peut le supprimer de gestion
                unset($this->selectedItems['gestion'][$type][$id]);
            }
            
            // Toujours supprimer de sauve lors du décochage
            unset($this->selectedItems['sauve'][$type][$id]);
            
            // Met à jour l'état dans la map seulement si ce n'est pas dans init
            if (!isset($this->selectedItems['init'][$type][$id])) {
                $this->map['descendant'][$type][$id]['etat_valid'] = false;
                $this->map['remontant'][$type][$id]['etat_valid'] = false;
            }
        }
    }
 
    /**
     * Sélectionne tous les descendants d'un élément
     */
    protected function selectDescendants(string $type, int $id, bool $etat): void
    {
        $childrenTypes = [
            'fonctions' => 'competences',
            'competences' => 'savoirfaires',
            'savoirfaires' => 'activites',
            'activites' => null,
        ];
 
        $childrenType = $childrenTypes[$type] ?? null;
        if (!$childrenType || !isset($this->map['descendant'][$type][$id][$childrenType])) {
            return;
        }
 
        foreach ($this->map['descendant'][$type][$id][$childrenType] as $childId) {
            // Met à jour l'état dans la map
            if (!isset($this->selectedItems['init'][$childrenType][$childId]) || $etat) {
                $this->map['descendant'][$childrenType][$childId]['etat_valid'] = $etat;
                $this->map['remontant'][$childrenType][$childId]['etat_valid'] = $etat;
            }
 
            if ($etat) {
                // Ajoute à la sélection
                $this->selectedItems['gestion'][$childrenType][$childId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init'][$childrenType][$childId])) {
                    $this->selectedItems['sauve'][$childrenType][$childId] = true;
                }
            } else {
                // Retire de la sélection selon les règles
                if (!isset($this->selectedItems['init'][$childrenType][$childId])) {
                    unset($this->selectedItems['gestion'][$childrenType][$childId]);
                }
                unset($this->selectedItems['sauve'][$childrenType][$childId]);
            }
 
            // Propage aux descendants
            $this->selectDescendants($childrenType, $childId, $etat);
        }
    }
 
    /**
     * Met à jour les parents selon l'état des enfants
     */
    protected function selectAscendants(string $type, int $id): void
    {
        $parentTypes = [
            'activites' => 'savoirfaires',
            'savoirfaires' => 'competences',
            'competences' => 'fonctions',
            'fonctions' => null,
        ];
 
        $parentType = $parentTypes[$type] ?? null;
        if (!$parentType || !isset($this->map['remontant'][$type][$id][$parentType])) {
            return;
        }
 
        foreach ($this->map['remontant'][$type][$id][$parentType] as $parentId) {
            // Récupération des enfants du parent
            $siblings = $this->map['descendant'][$parentType][$parentId][$type] ?? [];
            
            // Vérification si tous les enfants sont cochés
            $allSiblingsChecked = true;
            foreach ($siblings as $siblingId) {
                if (!isset($this->selectedItems['gestion'][$type][$siblingId])) {
                    $allSiblingsChecked = false;
                    break;
                }
            }
 
            // Met à jour l'état du parent dans la map
            if (!isset($this->selectedItems['init'][$parentType][$parentId]) || $allSiblingsChecked) {
                $this->map['descendant'][$parentType][$parentId]['etat_valid'] = $allSiblingsChecked;
                $this->map['remontant'][$parentType][$parentId]['etat_valid'] = $allSiblingsChecked;
            }
 
            if ($allSiblingsChecked) {
                // Si tous les enfants sont cochés, coche le parent
                $this->selectedItems['gestion'][$parentType][$parentId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init'][$parentType][$parentId])) {
                    $this->selectedItems['sauve'][$parentType][$parentId] = true;
                }
            } else {
                // Sinon, décoche le parent selon les règles
                if (!isset($this->selectedItems['init'][$parentType][$parentId])) {
                    unset($this->selectedItems['gestion'][$parentType][$parentId]);
                }
                unset($this->selectedItems['sauve'][$parentType][$parentId]);
            }
 
            // Propage aux ascendants
            $this->selectAscendants($parentType, $parentId);
        }
    }
 
    /**
     * Réinitialise les sélections à l'état initial
     */
    public function reinitialiserSelections(array &$map): array
    {
        $this->map = &$map;
        
        // Réinitialiser gestion pour qu'il corresponde à init
        $this->selectedItems['gestion'] = [];
        $this->selectedItems['sauve'] = [];
        
        // Recréer l'état initial des sélections dans 'gestion'
        if (isset($this->selectedItems['init'])) {
            foreach ($this->selectedItems['init'] as $type => $items) {
                foreach ($items as $id => $value) {
                    $this->selectedItems['gestion'][$type][$id] = $value;
                }
            }
        }
        
        // Réinitialiser l'état dans map pour correspondre à l'état initial
        $this->resetMapFromInitialState();
        
        return $this->selectedItems;
    }
 
    /**
     * Remet la map dans l'état initial
     */
    protected function resetMapFromInitialState(): void
    {
        // Réinitialiser tous les états à false dans la map
        $types = ['fonctions', 'competences', 'savoirfaires', 'activites'];
        
        foreach ($types as $type) {
            if (isset($this->map['descendant'][$type])) {
                foreach ($this->map['descendant'][$type] as $id => &$data) {
                    $data['etat_valid'] = false;
                }
            }
            
            if (isset($this->map['remontant'][$type])) {
                foreach ($this->map['remontant'][$type] as $id => &$data) {
                    $data['etat_valid'] = false;
                }
            }
        }
        
        // Appliquer les états depuis selectedItems['init']
        if (isset($this->selectedItems['init'])) {
            foreach ($this->selectedItems['init'] as $type => $items) {
                foreach ($items as $id => $value) {
                    if (isset($this->map['descendant'][$type][$id])) {
                        $this->map['descendant'][$type][$id]['etat_valid'] = true;
                    }
                    if (isset($this->map['remontant'][$type][$id])) {
                        $this->map['remontant'][$type][$id]['etat_valid'] = true;
                    }
                }
            }
        }
    }
 
    /**
     * Getters pour accéder aux données
     */
    public function getSelectedItems(): array
    {
        return $this->selectedItems;
    }
 
    public function setSelectedItems(array $selectedItems): void
    {
        $this->selectedItems = $selectedItems;
    }
 
    public function getInitItems(): array
    {
        return $this->selectedItems['init'] ?? [];
    }
 
    public function getGestionItems(): array
    {
        return $this->selectedItems['gestion'] ?? [];
    }
 
    public function getSauveItems(): array
    {
        return $this->selectedItems['sauve'] ?? [];
    }
 
    /**
     * Vérifie si un élément est dans l'état initial
     */
    public function isInitialItem(string $type, int $id): bool
    {
        return isset($this->selectedItems['init'][$type][$id]);
    }
 
    /**
     * Vérifie si un élément est coché en gestion
     */
    public function isChecked(string $type, int $id): bool
    {
        return isset($this->selectedItems['gestion'][$type][$id]);
    }



}
