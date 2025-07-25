<?php
 
namespace Modules\FcmCentral\Listeners\Save\MarinParcours;
 
use Filament\Notifications\Notification;
use App\Models\User;
use Modules\FcmCommun\Enums\Constants;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\Services\CalculTauxParcoursService;
use Modules\FcmCommun\Services\ReCalculEtatParcoursService; 
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\Log;
 
class AnnulElementMarinParcoursListener
{
    public $comment;
    public $selectedItems;
 
    /**
     * Gère l'événement d'annulation.
     *
     * @param UserGeneratedEvent $event
     * @return void
     */
    public function handle(UserGeneratedEvent $event): void
    {
        // Vérifier si l'événement est du bon type
        if (!$event->isOfType(UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS)) {
            return;
        }
 
        // Récupérer le marin et les données
        $marin = Marin::where('uuid', $event->object_uuid)->first();
        
        if (!$marin) {
            Log::error('Marin non trouvé avec UUID: ' . $event->object_uuid);
            return;
        }
 
        $data = $event->detail;
 
        // array:4 [▼ // vendor/spatie/laravel-ignition/src/helpers.php:14
        // "type_element" => "activite"
        // "uuid_element" => "eb48b9df-c466-37a5-a6b4-3251761f371f"
        // "date" => Carbon\Carbon @1753288270 {#2989 ▶}
        // "commentaire" => "dfg"
        // ]
        
        // RÉCUPÉRER TOUS LES ÉLÉMENTS DEPUIS selectedItems_full
        $this->comment = $this->sanitizeComment($data['commentaire'] ?? '');
        
        $valideur = User::where('uuid', $event->user_uuid)->first();
        $valideurName = $valideur ? 
            ($valideur->display_name ?? $valideur->nom . ' ' . $valideur->prenom) : 
            'ANNULEUR INCONNU';
 
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
 
        $this->updateParcoursMarinItems(
            $parcoursMarin, 
            $data['type_element'], 
            $data['uuid_element'],
            $valideurName
        );
 
        // RECALCULER LES ÉTATS DE VALIDATION HIÉRARCHIQUES
        ReCalculEtatParcoursService::recalculerEtats($parcoursMarin);
 
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
            UserGeneratedEventNotifications::ANNUL_ELMT_MARIN_PARCOURS,
            $marin->nom
        );
 
        Notification::make()
            ->title($notification['title'])
            ->warning() // ✅ WARNING au lieu de SUCCESS pour l'annulation
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
     * METTRE À JOUR LES ÉLÉMENTS POUR ANNULATION
     */
    protected function updateParcoursMarinItems(&$parcoursMarin, $type_element, $uuid_element, $displayName)
    { 
        logger()->info("On doit annuler : {$type_element} / {$uuid_element}");
        foreach ($parcoursMarin['fonctions'] as &$fonction) {
            logger()->info("Mise a jour de fonction: {$fonction["uuid"]}");
            // --- TRAITEMENT DIRECT DES FONCTIONS ---
            if ($type_element === 'fonction' && $fonction['uuid'] == $uuid_element) {
                $this->annulElement($fonction, $displayName);
                Log::info("Fonction annulée: {$fonction['uuid']}");
            }
 
            // --- TRAITEMENT DES COMPÉTENCES ---
            if (isset($fonction['competences'])) {
                foreach ($fonction['competences'] as &$competence) {
                    logger()->info("Mise a jour de competence: {$competence["uuid"]}");
                    if ($type_element === 'competence' && $competence['uuid'] == $uuid_element) {
                        $this->annulElement($competence, $displayName);
                        Log::info("Compétence annulée: {$competence['uuid']}");
                    }
 
                    // --- TRAITEMENT DES SAVOIR-FAIRES ---
                    if (isset($competence['savoirfaires'])) {
                        foreach ($competence['savoirfaires'] as &$savoirFaire) {
                            logger()->info("Mise a jour de savoir-faire: {$savoirFaire["uuid"]}");
                            if ($type_element === 'savoirfaire' && $savoirFaire['uuid'] == $uuid_element) {
                                $this->annulElement($savoirFaire, $displayName);
                                Log::info("Savoir-faire annulé: {$savoirFaire['uuid']}");
                            }
 
                            // --- TRAITEMENT DES ACTIVITÉS ---
                            if (isset($savoirFaire['activites'])) {
                                foreach ($savoirFaire['activites'] as &$activite) {
                                    logger()->info("Mise a jour de activite: {$activite["uuid"]}");
                                    if ($type_element === 'activite' && $activite['uuid'] == $uuid_element) {
                                        $this->annulElement($activite, $displayName);
                                        Log::info("Activité annulée: {$activite['uuid']}");
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
     * MÉTHODE HELPER POUR ANNULER UN ÉLÉMENT
     */
    protected function annulElement(&$element, $displayName)
    {
        $element['etat_valid'] = false; // ✅ DIFFÉRENCE PRINCIPALE : false au lieu de true
        $element['commentaire'] = $this->comment;
        $element['date_validation'] = \Carbon\Carbon::now()->toDateTimeString();
        $element['valideur'] = $displayName;
    }
}
