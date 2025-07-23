<?php
 
namespace Modules\FcmCentral\Listeners\Save\MarinParcours;
 
use Filament\Notifications\Notification;
use App\Models\User;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\Services\CalculTauxParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\Log;
 
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
        
        if (!$marin) {
            Log::error('Marin non trouvé avec UUID: ' . $event->object_uuid);
            return;
        }
 
        $data = $event->detail;
        //dd( $data);
        //  RÉCUPÉRER TOUS LES ÉLÉMENTS DEPUIS selectedItems_full
        $this->selectedItems = $data['sauve'] ?? [];
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');
        
        $valideur = User::where('uuid', $event->user_uuid)->first();
        $valideurName = $valideur ? 
            ($valideur->display_name ?? $valideur->nom . ' ' . $valideur->prenom) : 
            'VALIDEUR INCONNU';
 
        //  DÉTERMINER LA CLÉ D'ACTION (sauve, annul, propos, etc.)
        $actionType = $data['action_type'] ?? 'valid';
        //$key = $this->getKeyFromActionType($actionType);
 
        $donnees_du_parcours = $marin->complements_fcm->donnees_du_parcours;
        
        if (!$donnees_du_parcours) {
            Log::error('Données du parcours non trouvées pour le marin: ' . $marin->uuid);
            return;
        }
 
        $parcoursMarin = $donnees_du_parcours->parcoursmarin;
        
        if (!$parcoursMarin) {
            Log::error('Parcours marin non trouvé ou vide pour le marin: ' . $marin->uuid);
            return;
        }
 
        //  TRAITER TOUS LES ÉLÉMENTS SELON LA CLÉ D'ACTION
        $itemsToUpdate = $this->selectedItems ?? [];
        
        
 
        //  METTRE À JOUR TOUS LES TYPES D'ÉLÉMENTS
        foreach (['fonctions', 'competences', 'savoirfaires', 'activites'] as $elementType) {
            if (isset($itemsToUpdate[$elementType])) {
                $this->updateParcoursMarinItems(
                    $parcoursMarin, 
                    $elementType, 
                    $itemsToUpdate[$elementType], 
                    $valideurName
                );
            }
        }
 
        // Recalcul des taux
        $resultatCalcul = CalculTauxParcoursService::mettreAJourTaux($parcoursMarin);
        $donnees_du_parcours->taux_global = $parcoursMarin['taux_global'] ?? 0;
        $donnees_du_parcours->taux_stage = $parcoursMarin['taux_stage'] ?? 0;
        $donnees_du_parcours->taux_activite = $parcoursMarin['taux_activite'] ?? 0;
 
        // Sauvegarder les données
        $donnees_du_parcours->parcoursmarin = $parcoursMarin;
        $donnees_du_parcours->save();
 
        // Notification de succès
        $notification = UserGeneratedEventNotifications::getMess(
            UserGeneratedEventNotifications::VALID_ELMT_MARIN_PARCOURS,
            $marin->nom
        );
 
        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send();
    }
 
    
 
    /**
     * MÉTHODE POUR NETTOYER LES COMMENTAIRES
     */
    protected function sanitizeComment(string $comment): string
    {
        if (empty($comment)) {
            return '';
        }
        
        $comment = strip_tags($comment);
        $comment = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $comment);
        $comment = preg_replace('/\s+/', ' ', $comment);
        $comment = addslashes(trim($comment));
 
        return $comment;
    }
 
    /**
     *  METTRE À JOUR LES ÉLÉMENTS 
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type, $items, $displayName)
    {
        if (empty($items) || !is_array($items)) {
            return;
        }
 
        Log::info("Mise à jour des {$type}", [
            'count' => count($items),
            'items' => array_keys($items)
        ]);
 
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            // --- TRAITEMENT DIRECT DES FONCTIONS ---
            if ($type === 'fonctions' && isset($items[$fonction['uuid']])) {
                $this->updateElement($fonction, $displayName);
                Log::info("Fonction mise à jour: {$fonction['uuid']}");
            }
 
            // --- TRAITEMENT DES COMPÉTENCES ---
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    if ($type === 'competences' && isset($items[$competence['uuid']])) {
                        $this->updateElement($competence, $displayName);
                        Log::info("Compétence mise à jour: {$competence['uuid']}");
                    }
 
                    // --- TRAITEMENT DES SAVOIR-FAIRES ---
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            if ($type === 'savoirfaires' && isset($items[$savoirFaire['uuid']])) {
                                $this->updateElement($savoirFaire, $displayName);
                                Log::info("Savoir-faire mis à jour: {$savoirFaire['uuid']}");
                            }
 
                            // --- TRAITEMENT DES ACTIVITÉS ---
                            if (isset($savoirFaire['activites'])) {
                                foreach ($savoirFaire['activites'] as &$activite) {
                                    if ($type === 'activites' && isset($items[$activite['uuid']])) {
                                        $this->updateElement($activite, $displayName);
                                        Log::info("Activité mise à jour: {$activite['uuid']}");
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
     *  MÉTHODE HELPER POUR METTRE À JOUR UN ÉLÉMENT
     */
    protected function updateElement(&$element, $displayName)
    {
        $element['etat_valid'] = true;
        $element['commentaire'] = $this->comment;
        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
    }
}
