<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait HasDocumentsAnnexes
{
    // ✅ Documents annexes (hors parcours)
    public $nouveauDocumentAnnexe = null;
    public string $nomDocumentAnnexe = '';

    public function canAjouterDocumentAnnexe(): bool
    {
        return auth()->user()->can('fcmcentral::document_annexe.creer');
    }

    public function canSupprimerDocumentAnnexe(): bool
    {
        return auth()->user()->can('fcmcentral::document_annexe.supprimer');
    }

    #[Computed]
    public function documentsAnnexes()
    {
        $documents = collect($this->marin->complementsFcm?->documents_annexes ?? []);

        return $documents->map(function (array $doc) {
            $doc['url'] = route('fcmcentral.document-annexe.telecharger', [
                'marinUuid' => $this->marin->uuid,
                'documentUuid' => $doc['uuid'],
            ]);

            return $doc;
        })->sortByDesc('ajoute_le')->values();
    }

    public function updatedNouveauDocumentAnnexe(): void
    {
        $this->validate([
            'nouveauDocumentAnnexe' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'nouveauDocumentAnnexe.mimes' => 'Le document doit être un fichier PDF.',
            'nouveauDocumentAnnexe.max' => 'Le document ne doit pas dépasser 5 Mo.',
        ]);
    }

    public function ajouterDocumentAnnexe(): void
    {
        if (!$this->canAjouterDocumentAnnexe()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour ajouter un document annexe')
                ->warning()
                ->send()
            ;

            return;
        }

        if (!$this->nouveauDocumentAnnexe) {
            return;
        }

        $this->validate([
            'nouveauDocumentAnnexe' => ['file', 'mimes:pdf', 'max:5120'],
            'nomDocumentAnnexe' => ['required', 'string', 'max:150'],
        ], [
            'nouveauDocumentAnnexe.mimes' => 'Le document doit être un fichier PDF.',
            'nouveauDocumentAnnexe.max' => 'Le document ne doit pas dépasser 5 Mo.',
            'nomDocumentAnnexe.required' => 'Merci de donner un nom à ce document.',
        ]);

        $directory = sprintf(
            'fcmcentral/documents-annexes/marins/%s/%s/%s',
            $this->marinId,
            now()->format('Y'),
            now()->format('m')
        );
        $chemin = $this->nouveauDocumentAnnexe->store($directory, 's3-private');

        $fcmMarin = $this->marin->complementsFcm;
        $documents = $fcmMarin->documents_annexes ?? [];
        $documents[] = [
            'uuid' => (string) Str::uuid(),
            'nom' => $this->nomDocumentAnnexe,
            'chemin' => $chemin,
            'ajoute_par' => auth()->user()->display_name ?? auth()->user()->name,
            'ajoute_le' => now()->toDateTimeString(),
        ];
        $fcmMarin->documents_annexes = $documents;
        $fcmMarin->save();

        $this->nouveauDocumentAnnexe = null;
        $this->nomDocumentAnnexe = '';

        Notification::make()->title('Document ajouté')->success()->send();

        unset($this->documentsAnnexes);
        $this->dispatch('close-modal', id: 'ajouter-document-annexe');
        $this->dispatch('$refresh');
    }

    public function supprimerDocumentAnnexe(string $uuid): void
    {
        if (!$this->canSupprimerDocumentAnnexe()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour supprimer un document annexe')
                ->warning()
                ->send()
            ;

            return;
        }

        $fcmMarin = $this->marin->complementsFcm;
        $documents = collect($fcmMarin->documents_annexes ?? []);
        $document = $documents->firstWhere('uuid', $uuid);

        if (!$document) {
            return;
        }

        if (!$this->supprimerFichierS3($document['chemin'] ?? null)) {
            Notification::make()
                ->title('Échec de la suppression')
                ->body('Le fichier n\'a pas pu être supprimé du stockage. Réessayez.')
                ->danger()
                ->send()
            ;

            return;
        }

        $fcmMarin->documents_annexes = $documents->reject(fn (array $d) => $d['uuid'] === $uuid)->values()->toArray();
        $fcmMarin->save();

        Notification::make()->title('Document supprimé')->success()->send();

        unset($this->documentsAnnexes);
        $this->dispatch('$refresh');
    }

    /**
     * Supprime un fichier du disque s3-private et vérifie que la suppression a bien eu lieu.
     * Retourne true si le fichier n'existe plus (ou n'existait pas), false si la suppression a échoué.
     */
    private function supprimerFichierS3(?string $chemin): bool
    {
        if (!$chemin) {
            return true;
        }

        $disk = Storage::disk('s3-private');

        if (!$disk->exists($chemin)) {
            return true;
        }

        $disk->delete($chemin);

        return !$disk->exists($chemin);
    }
}
