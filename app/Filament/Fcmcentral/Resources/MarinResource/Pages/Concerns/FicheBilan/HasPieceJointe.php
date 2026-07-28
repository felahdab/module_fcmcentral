<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Filament\Notifications\Notification;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

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

        // ✅ Passe par l'event/listener (même schéma fiable que valider()/devalider()), qui
        // retrouve le chemin S3 actuel, le supprime et retire la référence du JSON.
        event(new UserGeneratedEvent(
            UserGeneratedEventTypes::SUPPR_PIECE_JOINTE_MARIN_PARCOURS,
            auth()->user()->uuid,
            ObjectClasses::MARIN,
            $this->marin->uuid,
            [
                'type_element' => 'savoirfaire',
                'uuid_element' => $csfUuid,
            ]
        ));

        $this->marin->refresh();
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
            'fcmcentral/livretfcm/pieces-jointes/savoirfaire/%s/%s',
            now()->format('Y'),
            now()->format('m')
        );
        $nouveauChemin = $this->nouvellePieceJointe->store($directory, 's3-public');

        // ✅ Passe par l'event/listener (même schéma fiable que valider()/devalider()), qui fait
        // la mutation JSON du parcours en base et nettoie l'éventuel ancien fichier remplacé.
        event(new UserGeneratedEvent(
            UserGeneratedEventTypes::AJOUT_PIECE_JOINTE_MARIN_PARCOURS,
            auth()->user()->uuid,
            ObjectClasses::MARIN,
            $this->marin->uuid,
            [
                'type_element' => 'savoirfaire',
                'uuid_element' => $this->pieceJointeEnEditionUuid,
                'piece_jointe' => $nouveauChemin,
            ]
        ));

        $this->pieceJointeEnEditionType = null;
        $this->pieceJointeEnEditionUuid = null;
        $this->nouvellePieceJointe = null;

        $this->marin->refresh();
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

}
