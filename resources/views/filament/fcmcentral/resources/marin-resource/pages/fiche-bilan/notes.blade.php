<x-filament::section class="section-card">
    <x-slot name="heading">
        <div class="flex items-center justify-between">
            <span>Commentaires et observations ({{ $this->notes->count() }})</span>
        </div>
    </x-slot>
    @if($this->notes->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-sm">Aucun commentaire enregistré</p>
        </div>
    @else
        <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
            @foreach($this->notes as $note)
                <div class="border-l-4 {{ $note->valide_bcf ? 'border-green-200 bg-green-200' : 'border-blue-500 bg-blue-50' }} p-4 rounded-r note-card">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h5 class="font-medium text-sm text-gray-900">
                                {{ $note->typeNote?->libelle_court ?? 'Commentaire' }}
                            </h5>
                            @if($note->date)
                                <p class="text-xs text-gray-500 mt-1">
                                    📅 {{ \Carbon\Carbon::parse($note->date)->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right ml-4">
                            @if($note->redacteur)
                                <div class="text-xs text-gray-600 mb-1">
                                    {{ $note->redacteur ?? 'Inconnu' }}
                                </div>
                            @endif
                            @if($note->valide_bcf)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800">
                                    ✓ Validé BCF
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $note->libelle }}</p>
                    @if($note->lienurl)
                        <a href="{{ $note->lienurl }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline mt-2 inline-block">
                            🔗 Voir le lien associé ↗
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
