<x-filament::section class="section-card">
    <x-slot name="heading">
        <div class="flex items-center justify-between gap-4">
            <span>Progression par savoir-faire ({{ $this->savoirFaires->count() }})</span>
            <div class="flex items-center gap-2">
                <select
                    wire:change="filtrerParStatutSavoirFaire($event.target.value)"
                    class="text-xs rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="tous" @selected('tous' === $savoirFaireStatutFilter)>Tous statuts</option>
                    <option value="valide" @selected('valide' === $savoirFaireStatutFilter)>Validés</option>
                    <option value="non_valide" @selected('non_valide' === $savoirFaireStatutFilter)>Non validés</option>
                </select>
                <select
                    wire:change="filtrerParTypeSavoirFaire($event.target.value)"
                    class="text-xs rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="tous" @selected('tous' === $savoirFaireTypeFilter)>Tous types</option>
                    <option value="S" @selected('S' === $savoirFaireTypeFilter)>Stages</option>
                    <option value="EAD" @selected('EAD' === $savoirFaireTypeFilter)>EAD</option>
                    <option value="C" @selected('C' === $savoirFaireTypeFilter)>Objectifs (Compagnonnage)</option>
                </select>
            </div>
        </div>
    </x-slot>
    <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar" x-data="{ ouvertes: {} }">
        @forelse($this->savoirFaires->groupBy('affectation') as $affectation => $savoirFairesByAffectation)
            @php($affectationKey = \Illuminate\Support\Str::slug($affectation))
            <div class="sf-group-header cursor-pointer select-none"
                 x-on:click="ouvertes['{{ $affectationKey }}'] = !ouvertes['{{ $affectationKey }}']">
                <h4 class="font-semibold text-sm text-adaptive-dark flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                        </svg>
                        {{ $affectation }}
                        <span class="ml-2 text-xs font-normal text-adaptive-gray">({{ $savoirFairesByAffectation->count() }})</span>
                    </span>
                    <svg class="w-4 h-4 text-adaptive-gray transition-transform"
                         x-bind:class="ouvertes['{{ $affectationKey }}'] ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </h4>
            </div>
            <div x-show="ouvertes['{{ $affectationKey }}']" x-collapse x-cloak class="space-y-3">
            @foreach($savoirFairesByAffectation as $savoirFaire)
                <div class="sf-item {{ $savoirFaire['couleur_classe']['bg'] }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h5 class="text-xs font-medium text-adaptive-dark">
                                {{ $savoirFaire['libelle_court'] }}
                            </h5>

                                @if(($savoirFaire['savoirfaire_type']!='C')&&$savoirFaire['intitule'])
                                <h6 class="text-xs font-medium text-adaptive-dark">{{$savoirFaire['intitule']}}</h6>
                                    @endif
                            <p class="text-xs text-adaptive-gray mt-1">{{ $savoirFaire['competence'] }}</p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if($savoirFaire['etat_valid'])
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            <span class="text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap {{ $savoirFaire['couleur_classe']['bg'] }} {{ $savoirFaire['couleur_classe']['text'] }}">
                                {{ $savoirFaire['pourcentage'] }}%
                            </span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="w-full progress-bg rounded-full h-2">
                            <div class="h-2 rounded-full progress-bar {{ str_replace('bg-', 'progress-', $savoirFaire['couleur_classe']['progress']) }}"
                                style="width: {{ $savoirFaire['pourcentage'] }}%">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-xs text-adaptive-gray">
                        <span> {{ $savoirFaire['savoirfaire_type_libelle'] }} </span>
                        @if($savoirFaire['savoirfaire_type']=='C')
                        <span>
                            <strong class="text-adaptive-dark">{{ $savoirFaire['objectifs_valides'] }}</strong> / {{ $savoirFaire['total_objectifs'] }} objectifs
                        </span>
                        @endif
                        @if($savoirFaire['etat_valid'] && $savoirFaire['date_validation'])
                            <span class=" font-medium">
                               <b class="text-green-600"> ✓ {{ \Carbon\Carbon::parse($savoirFaire['date_validation'])->format('d/m/Y') }}</b><br/>
                                {{ $savoirFaire['commentaire'] }} par  {{$savoirFaire['valideur'] }}
                            </span>

                        @endif
                    </div>

                    {{-- ✅ Pièce jointe (diplôme, justificatif) — uniquement si le savoir-faire est validé --}}
                    @if($savoirFaire['csf_uuid'] && $savoirFaire['etat_valid'])
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            @if($savoirFaire['piece_jointe_url'])
                                <a href="{{ $savoirFaire['piece_jointe_url'] }}" target="_blank" rel="noopener"
                                   class="text-xs text-primary-600 hover:underline flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-paper-clip" class="w-3.5 h-3.5" />
                                    Voir la pièce jointe
                                </a>
                                <button type="button" class="text-xs text-danger-600 hover:underline"
                                        wire:click="supprimerPieceJointe('{{ $savoirFaire['csf_uuid'] }}')"
                                        wire:confirm="Supprimer définitivement cette pièce jointe ?">
                                    Supprimer
                                </button>
                            @else
                                <span class="text-xs text-gray-400">Pas de pièce jointe</span>
                                <button type="button" class="text-xs text-primary-600 hover:underline"
                                        wire:click="ouvrirAjoutPieceJointe('{{ $savoirFaire['csf_uuid'] }}')">
                                    Ajouter un document
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
        @empty
            <div class="text-center py-8 text-adaptive-gray">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                </svg>
                <p>Aucun savoir-faire dans ce parcours</p>
            </div>
        @endforelse
    </div>
</x-filament::section>
