<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

trait HasPieceJointe
{
    // ✅ Ajout de pièce jointe : élément en cours d'édition
    public ?string $pieceJointeEnEditionType = null;
    public ?string $pieceJointeEnEditionUuid = null;
    public $nouvellePieceJointe = null;

    public function ouvrirAjoutPieceJointe(string $csfUuid): void
    {
        if (!$this->csfEstValide($csfUuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être ajoutée que sur un savoir-faire validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        $this->pieceJointeEnEditionType = 'savoirfaire';
        $this->pieceJointeEnEditionUuid = $csfUuid;
        $this->nouvellePieceJointe = null;
        $this->dispatch('open-modal', id: 'ajouter-piece-jointe');
    }

    public function updatedNouvellePieceJointe(): void
    {
        $this->validate([
            'nouvellePieceJointe' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'nouvellePieceJointe.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'nouvellePieceJointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);
    }

    /**
     * Supprime la pièce jointe d'un savoir-faire (fichier S3 + référence dans le parcours).
     */
    public function supprimerPieceJointe(string $csfUuid): void
    {
        if (!$this->canCreateNote() && !auth()->user()->can('fcmcentral::livret.valider')) {
            Notification::make()->title('Accès refusé')->warning()->send();

            return;
        }

        if (!$this->csfEstValide($csfUuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être supprimée que sur un savoir-faire validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        if (!$this->supprimerFichierS3($this->savoirFaires->first(fn (array $sf) => ($sf['csf_uuid'] ?? null) === $csfUuid)['piece_jointe'] ?? null)) {
            Notification::make()
                ->title('Échec de la suppression')
                ->body('Le fichier n\'a pas pu être supprimé du stockage. Réessayez.')
                ->danger()
                ->send()
            ;

            return;
        }

        $this->mutateCsfParUuid($csfUuid, function (array &$csf): void {
            unset($csf['piece_jointe']);
        });

        Notification::make()->title('Pièce jointe supprimée')->success()->send();

        unset($this->parcours);
        unset($this->savoirFaires);
        $this->dispatch('$refresh');
    }

    /**
     * Ajoute une pièce jointe sur un savoir-faire qui n'en a pas encore.
     * (Le remplacement n'existe pas comme action séparée : il faut d'abord
     * supprimer la pièce jointe existante avant de pouvoir en ajouter une nouvelle.)
     */
    public function ajouterPieceJointe(): void
    {
        if (!$this->pieceJointeEnEditionUuid || !$this->nouvellePieceJointe) {
            return;
        }

        if (!$this->csfEstValide($this->pieceJointeEnEditionUuid)) {
            Notification::make()
                ->title('Action impossible')
                ->body('La pièce jointe ne peut être ajoutée que sur un savoir-faire validé.')
                ->warning()
                ->send()
            ;

            return;
        }

        $directory = sprintf(
            'fcmcommun/livretfcm/pieces-jointes/savoirfaire/%s/%s',
            now()->format('Y'),
            now()->format('m')
        );
        $nouveauChemin = $this->nouvellePieceJointe->store($directory, 's3-public');

        $this->mutateCsfParUuid($this->pieceJointeEnEditionUuid, function (array &$csf) use ($nouveauChemin): void {
            $csf['piece_jointe'] = $nouveauChemin;
        });

        $this->pieceJointeEnEditionType = null;
        $this->pieceJointeEnEditionUuid = null;
        $this->nouvellePieceJointe = null;

        Notification::make()->title('Pièce jointe ajoutée')->success()->send();

        unset($this->parcours);
        unset($this->savoirFaires);
        $this->dispatch('close-modal', id: 'ajouter-piece-jointe');
        $this->dispatch('$refresh');
    }

    /**
     * Vérifie que le savoir-faire identifié par $uuid est bien validé (etat_valid),
     * seule condition dans laquelle la pièce jointe peut être ajoutée/remplacée/supprimée.
     */
    private function csfEstValide(string $uuid): bool
    {
        $savoirFaire = $this->savoirFaires->first(fn (array $sf) => ($sf['csf_uuid'] ?? null) === $uuid);

        return (bool) ($savoirFaire['etat_valid'] ?? false);
    }

    /**
     * Trouve le competenceSavoirfaire correspondant à $uuid dans le parcours du marin
     * et applique $callback dessus par référence, puis sauvegarde.
     */
    private function mutateCsfParUuid(string $uuid, callable $callback): bool
    {
        $donneesDuParcours = $this->marin->complementsFcm?->donnees_du_parcours;
        $parcoursMarin = $donneesDuParcours?->parcoursmarin ?? null;

        if (!is_array($parcoursMarin)) {
            return false;
        }

        foreach ($parcoursMarin['affectations'] ?? [] as &$affectation) {
            foreach ($affectation['competences'] ?? [] as &$competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as &$csf) {
                    if (($csf['uuid'] ?? null) === $uuid) {
                        $callback($csf);

                        $donneesDuParcours->parcoursmarin = $parcoursMarin;
                        $donneesDuParcours->save();

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Supprime un fichier du disque s3-public et vérifie que la suppression a bien eu lieu.
     * Retourne true si le fichier n'existe plus (ou n'existait pas), false si la suppression a échoué.
     */
    private function supprimerFichierS3(?string $chemin): bool
    {
        if (!$chemin) {
            return true;
        }

        $disk = Storage::disk('s3-public');

        if (!$disk->exists($chemin)) {
            return true;
        }

        $disk->delete($chemin);

        return !$disk->exists($chemin);
    }
}
