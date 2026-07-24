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

    #[Computed]
    public function documentsAnnexes()
    {
        $documents = collect($this->marin->complementsFcm?->documents_annexes ?? []);

        return $documents->map(function (array $doc) {
            $doc['url'] = Storage::disk('s3-public')->url($doc['chemin']);

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
            'fcmcommun/documents-annexes/marins/%s/%s/%s',
            $this->marinId,
            now()->format('Y'),
            now()->format('m')
        );
        $chemin = $this->nouveauDocumentAnnexe->store($directory, 's3-public');

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
}
