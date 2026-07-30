<?php

namespace Modules\FcmCentral\Listeners\Save\MarinParcours;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventNotifications;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

/**
 * Gère l'ajout/la suppression d'une pièce jointe sur un élément (savoir-faire ou objectif)
 * déjà validé du parcours d'un marin. Suit le même schéma fiable que
 * ValidElementMarinParcoursListener / AnnulElementMarinParcoursListener : mutation directe
 * du JSON parcoursmarin par référence (jamais de `?? []` dans un foreach par référence,
 * qui casse silencieusement la référence et fait perdre la mutation malgré un save() à `true`).
 */
class PieceJointeMarinParcoursListener
{
    public function handle(UserGeneratedEvent $event): void
    {
        if (
            !$event->isOfType(UserGeneratedEventTypes::AJOUT_PIECE_JOINTE_MARIN_PARCOURS)
            && !$event->isOfType(UserGeneratedEventTypes::SUPPR_PIECE_JOINTE_MARIN_PARCOURS)
        ) {
            return;
        }

        $estAjout = $event->isOfType(UserGeneratedEventTypes::AJOUT_PIECE_JOINTE_MARIN_PARCOURS);

        $marin = Marin::where('uuid', $event->object_uuid)->first();
        if (!$marin) {
            Log::error('[PieceJointeMarinParcoursListener] Marin non trouvé: '.$event->object_uuid);

            return;
        }

        $data = $event->detail ?? [];
        $type = $data['type_element'] ?? null;
        $uuid = $data['uuid_element'] ?? null;
        $cheminPieceJointe = $data['piece_jointe'] ?? null;

        if (!$type || !$uuid) {
            Log::error('[PieceJointeMarinParcoursListener] Payload invalide: '.json_encode($data));

            return;
        }

        if ($estAjout && !$cheminPieceJointe) {
            Log::error('[PieceJointeMarinParcoursListener] Ajout demandé sans chemin de fichier: '.json_encode($data));

            return;
        }

        $donneesDuParcours = $marin->complements_fcm->donnees_du_parcours ?? null;
        if (!$donneesDuParcours) {
            Log::error('[PieceJointeMarinParcoursListener] Données du parcours non trouvées pour le marin: '.$marin->uuid);

            return;
        }

        $parcoursMarin = $donneesDuParcours->parcoursmarin ?? null;
        if (!is_array($parcoursMarin)) {
            Log::error('[PieceJointeMarinParcoursListener] Parcours marin non trouvé ou invalide: '.$marin->uuid);

            return;
        }

        Log::info('=== PIECE JOINTE ('.($estAjout ? 'ajout' : 'suppression').") === type={$type} uuid={$uuid} marin={$marin->uuid}");

        // Chemin S3 à nettoyer si l'élément avait déjà une pièce jointe et qu'on la remplace,
        // ou si on la supprime.
        $ancienChemin = null;

        $updated = $this->mutateElement($parcoursMarin, $type, $uuid, function (array &$element) use ($estAjout, $cheminPieceJointe, &$ancienChemin): void {
            $ancienChemin = $element['piece_jointe'] ?? null;

            if ($estAjout) {
                $element['piece_jointe'] = $cheminPieceJointe;
            } else {
                unset($element['piece_jointe']);
            }
        });

        if (!$updated) {
            Log::error("❌ [PieceJointeMarinParcoursListener] ÉLÉMENT NON TROUVÉ: {$type} / {$uuid}");

            // On vient de stocker un nouveau fichier sur S3 sans pouvoir l'attacher : on nettoie l'orphelin.
            if ($estAjout && $cheminPieceJointe) {
                Storage::disk('s3-private')->delete($cheminPieceJointe);
            }

            return;
        }

        $donneesDuParcours->parcoursmarin = $parcoursMarin;
        $sauvegarde = $donneesDuParcours->save();

        // Preuve : relecture d'une instance fraîche depuis la base.
        $fraiche = $donneesDuParcours->fresh();
        $elementApresRelecture = $this->trouverElementBrut($fraiche?->parcoursmarin ?? [], $type, $uuid);

        Log::info('[PieceJointeMarinParcoursListener] résultat', [
            'type' => $type,
            'uuid' => $uuid,
            'marinParcoursId' => $donneesDuParcours->id,
            'saveResult' => $sauvegarde,
            'pieceJointeApresRelecture' => $elementApresRelecture['piece_jointe'] ?? '(absent)',
        ]);

        // Suppression physique du fichier S3 devenu orphelin (suppression, ou remplacement d'un ancien fichier).
        if (!$estAjout && $ancienChemin) {
            Storage::disk('s3-private')->delete($ancienChemin);
        } elseif ($estAjout && $ancienChemin && $ancienChemin !== $cheminPieceJointe) {
            Storage::disk('s3-private')->delete($ancienChemin);
        }

        $notificationType = $estAjout
            ? UserGeneratedEventNotifications::AJOUT_PIECE_JOINTE_MARIN_PARCOURS
            : UserGeneratedEventNotifications::SUPPR_PIECE_JOINTE_MARIN_PARCOURS;

        $notification = UserGeneratedEventNotifications::getMess($notificationType, $marin->nom);

        Notification::make()
            ->title($notification['title'])
            ->success()
            ->body($notification['message'])
            ->send()
        ;
    }

    /**
     * Trouve l'élément (savoirfaire ou objectif) correspondant à $uuid et applique $callback
     * dessus par référence. Ne jamais écrire `$parcoursMarin['affectations'] ?? []` dans
     * l'expression itérée d'un foreach par référence : `??` renvoie une valeur temporaire,
     * ce qui casse le lien de référence et fait perdre toute mutation silencieusement.
     */
    private function mutateElement(array &$parcoursMarin, string $type, string $uuid, callable $callback): bool
    {
        $parcoursMarin['affectations'] = $parcoursMarin['affectations'] ?? [];

        foreach ($parcoursMarin['affectations'] as &$affectation) {
            $affectation['competences'] = $affectation['competences'] ?? [];

            foreach ($affectation['competences'] as &$competence) {
                $competence['competenceSavoirfaires'] = $competence['competenceSavoirfaires'] ?? [];

                foreach ($competence['competenceSavoirfaires'] as &$csf) {
                    if ('savoirfaire' === $type && ($csf['uuid'] ?? null) === $uuid) {
                        $callback($csf);

                        return true;
                    }

                    if ('objectif' === $type) {
                        $csf['objectifs'] = $csf['objectifs'] ?? [];

                        foreach ($csf['objectifs'] as &$objectif) {
                            if (($objectif['uuid'] ?? null) === $uuid) {
                                $callback($objectif);

                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Lecture seule (pour la vérification post-sauvegarde) : pas de référence, `??` sans risque ici.
     */
    private function trouverElementBrut(array $parcoursMarin, string $type, string $uuid): ?array
    {
        foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if ('savoirfaire' === $type && ($csf['uuid'] ?? null) === $uuid) {
                        return $csf;
                    }

                    if ('objectif' === $type) {
                        foreach ($csf['objectifs'] ?? [] as $objectif) {
                            if (($objectif['uuid'] ?? null) === $uuid) {
                                return $objectif;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
