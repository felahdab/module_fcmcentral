{{-- MODAL MODIFICATION JALONS --}}
<x-filament::modal
    id="jalon-modal"
    :visible="$showJalonModal"
    wire:model="showJalonModal"
>
    <x-slot name="heading">
        Modifier les jalons FCM
    </x-slot>

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date d'entrée en FCM</label>
            <input
                type="date"
                wire:model="jalonForm.date_sedimentation"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date de fin de sédimentation</label>
            <input
                type="date"
                wire:model="jalonForm.date_fcm"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date de vert compétence</label>
            <input
                type="date"
                wire:model="jalonForm.date_competence"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
        </div>
    </div>

    <x-slot name="footerActions">
        <x-filament::button wire:click="sauvegarderJalons" color="primary">
            Enregistrer
        </x-filament::button>
        <x-filament::button wire:click="$set('showJalonModal', false)" color="gray">
            Annuler
        </x-filament::button>
    </x-slot>
</x-filament::modal>

{{-- MODAL AJOUT PIÈCE JOINTE --}}
<x-filament::modal id="ajouter-piece-jointe" width="md">
    <x-slot name="heading">
        Pièce jointe du savoir-faire
    </x-slot>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Document (PDF, 5 Mo maximum)
        </label>
        <input
            type="file"
            wire:model="nouvellePieceJointe"
            accept="application/pdf"
            class="w-full text-sm text-gray-700 dark:text-gray-300 border-gray-300 rounded-md shadow-sm
                   focus:ring-primary-500 focus:border-primary-500
                   dark:bg-gray-700 dark:border-gray-600"
        />
        <div wire:loading wire:target="nouvellePieceJointe" class="text-xs text-gray-500">
            Envoi du fichier en cours...
        </div>
        @error('nouvellePieceJointe')
            <p class="text-xs text-danger-600">{{ $message }}</p>
        @enderror
    </div>

    <x-slot name="footerActions">
        <x-filament::button wire:click="ajouterPieceJointe" color="primary" :disabled="!$nouvellePieceJointe">
            Enregistrer
        </x-filament::button>
        <x-filament::button wire:click="$dispatch('close-modal', { id: 'ajouter-piece-jointe' })" color="gray">
            Annuler
        </x-filament::button>
    </x-slot>
</x-filament::modal>

{{-- MODAL AJOUT DOCUMENT ANNEXE --}}
<x-filament::modal id="ajouter-document-annexe" width="md">
    <x-slot name="heading">
        Ajouter un document annexe
    </x-slot>

    <div class="space-y-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du document</label>
            <input
                type="text"
                wire:model="nomDocumentAnnexe"
                placeholder="Ex : Attestation de formation, courrier..."
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
            @error('nomDocumentAnnexe')
                <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document (PDF, 5 Mo maximum)</label>
            <input
                type="file"
                wire:model="nouveauDocumentAnnexe"
                accept="application/pdf"
                class="w-full text-sm text-gray-700 dark:text-gray-300 border-gray-300 rounded-md shadow-sm
                       focus:ring-primary-500 focus:border-primary-500
                       dark:bg-gray-700 dark:border-gray-600"
            />
            <div wire:loading wire:target="nouveauDocumentAnnexe" class="text-xs text-gray-500 mt-1">
                Envoi du fichier en cours...
            </div>
            @error('nouveauDocumentAnnexe')
                <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <x-slot name="footerActions">
        <x-filament::button wire:click="ajouterDocumentAnnexe" color="primary" :disabled="!$nouveauDocumentAnnexe || !$nomDocumentAnnexe">
            Enregistrer
        </x-filament::button>
        <x-filament::button wire:click="$dispatch('close-modal', { id: 'ajouter-document-annexe' })" color="gray">
            Annuler
        </x-filament::button>
    </x-slot>
</x-filament::modal>
