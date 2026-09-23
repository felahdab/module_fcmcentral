<x-filament::section class="section-card mb-6">
    <x-slot name="heading">
        <div class="flex items-center justify-between">
            <span>Documents annexes ({{ $this->documentsAnnexes->count() }})</span>
            @if($this->canAjouterDocumentAnnexe())
                <x-filament::button
                    size="sm"
                    color="primary"
                    icon="heroicon-o-plus-circle"
                    wire:click="$dispatch('open-modal', { id: 'ajouter-document-annexe' })"
                >
                    Ajouter
                </x-filament::button>
            @endif
        </div>
    </x-slot>

    @if($this->documentsAnnexes->isEmpty())
        <div class="text-center py-6 text-gray-500 text-sm">
            Aucun document annexe pour ce marin.
        </div>
    @else
        <div class="space-y-2">
            @foreach($this->documentsAnnexes as $document)
                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                    <a href="{{ $document['url'] }}" target="_blank" rel="noopener"
                       class="text-sm text-primary-600 hover:underline flex items-center gap-2">
                        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.pdf-icon', ['class' => 'w-4 h-4'])
                        <span>
                            {{ $document['nom'] }}
                            <span class="text-xs text-gray-400 block">
                                Ajouté le {{ \Carbon\Carbon::parse($document['ajoute_le'])->format('d/m/Y H:i') }} par {{ $document['ajoute_par'] }}
                            </span>
                        </span>
                    </a>
                    @if($this->canSupprimerDocumentAnnexe())
                        <button type="button" class="text-xs text-danger-600 hover:underline flex-shrink-0"
                                wire:click="supprimerDocumentAnnexe('{{ $document['uuid'] }}')"
                                wire:confirm="Supprimer définitivement ce document ?">
                            Supprimer
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
