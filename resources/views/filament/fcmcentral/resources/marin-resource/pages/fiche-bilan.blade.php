<x-filament-panels::page>
    @if(!$this->marin)
        <x-filament::section>
            <div class="text-center py-8">
                <div class="text-red-600 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Marin introuvable</h3>
                <p class="text-gray-500">Le marin avec l'ID {{ $marinId }} n'existe pas.</p>
            </div>
        </x-filament::section>
    @else
        {{-- En-tête avec informations du marin --}}
        <div class="mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            {{ $this->marin->grade?->libelle_court }} {{ $this->marin->prenom }} {{ $this->marin->nom }}
                        </h1>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm opacity-90">
                            <div><strong>NID:</strong> {{ $this->marin->nid }}</div>
                            <div><strong>Spécialité:</strong> {{ $this->marin->specialite?->libelle_court ?? 'N/A' }}</div>
                            <div><strong>Unité:</strong> {{ $this->marin->unite?->libelle_court ?? 'N/A' }}</div>
                            <div><strong>Cohorte:</strong> {{ $this->marin->complementsFcm?->cohorte?->libelle_court ?? 'N/A' }}</div>
                        </div>
                        @if($this->marin->complementsFcm?->mentor)
                            <div class="mt-2 text-sm opacity-90">
                                <strong>Mentor:</strong> {{ $this->marin->complementsFcm->mentor->grade?->libelle_court }} {{ $this->marin->complementsFcm->mentor->prenom }} {{ $this->marin->complementsFcm->mentor->nom }}
                            </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-90">Fiche générée le</div>
                        <div class="text-lg font-semibold">{{ now()->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$this->parcours)
            <x-filament::section>
                <div class="text-center py-8">
                    <div class="text-yellow-600 mb-2">
                        <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun parcours assigné</h3>
                    <p class="text-gray-500">Ce marin n'a pas encore été affecté à un parcours FCM.</p>
                </div>
            </x-filament::section>
        @else
            {{-- Statistiques principales --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <x-filament::section class="text-center">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-green-600">{{ $this->statistiques['pourcentage_global'] }}%</div>
                        <div class="text-sm text-gray-600 mt-1">Progression Globale</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-blue-600">{{ $this->statistiques['activites_validees'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">Activités Validées</div>
                        <div class="text-xs text-gray-500">/ {{ $this->statistiques['total_activites'] }} total</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-purple-600">{{ $this->statistiques['savoirfaires_valides'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">Savoir-Faire Validés</div>
                        <div class="text-xs text-gray-500">/ {{ $this->statistiques['total_savoirfaires'] }} total</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-orange-600">{{ $this->activitesEnAttente->count() }}</div>
                        <div class="text-sm text-gray-600 mt-1">En Attente</div>
                        <div class="text-xs text-gray-500">de validation</div>
                    </div>
                </x-filament::section>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Progression par savoir-faire --}}
                <x-filament::section>
                    <x-slot name="heading">
                        Progression par Savoir-Faire ({{ $this->savoirFaires->count() }})
                    </x-slot>

                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($this->savoirFaires->groupBy('fonction') as $fonction => $savoirFairesByFonction)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-semibold text-sm text-gray-800 mb-3">{{ $fonction }}</h4>
                                
                                @foreach($savoirFairesByFonction as $savoirFaire)
                                    <div class="mb-3 pb-3 border-b border-gray-100 last:border-b-0">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <h5 class="text-xs font-medium text-gray-900">
                                                    {{ $savoirFaire['libelle_court'] }}
                                                </h5>
                                                <p class="text-xs text-gray-500">{{ $savoirFaire['competence'] }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($savoirFaire['etat_valid'])
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                                <span class="text-xs px-2 py-1 rounded-full 
                                                    {{ $savoirFaire['pourcentage'] >= 80 ? 'bg-green-100 text-green-800' : 
                                                       ($savoirFaire['pourcentage'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $savoirFaire['pourcentage'] }}%
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-1">
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full
                                                    {{ $savoirFaire['pourcentage'] >= 80 ? 'bg-green-500' : 
                                                       ($savoirFaire['pourcentage'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                    style="width: {{ $savoirFaire['pourcentage'] }}%">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs text-gray-600">
                                            {{ $savoirFaire['activites_validees'] }}/{{ $savoirFaire['total_activites'] }} activités
                                            @if($savoirFaire['etat_valid'] && $savoirFaire['date_validation'])
                                                - Validé le {{ \Carbon\Carbon::parse($savoirFaire['date_validation'])->format('d/m/Y') }}
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>

                {{-- Activités en attente --}}
                <x-filament::section>
                    <x-slot name="heading">
                        Activités en Attente de Validation ({{ $this->activitesEnAttente->count() }})
                    </x-slot>

                    @if($this->activitesEnAttente->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <p>Aucune activité en attente</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($this->activitesEnAttente as $item)
                                <div class="border-l-4 border-orange-500 pl-4 py-2 bg-orange-50 rounded-r">
                                    <h5 class="font-medium text-sm text-gray-900">
                                        {{ $item['activite']['libelle_court'] }}
                                    </h5>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{ $item['fonction'] }} > {{ $item['competence'] }} > {{ $item['savoirfaire'] }}
                                    </p>
                                    <p class="text-xs text-orange-600 mt-1">
                                        Proposée le {{ \Carbon\Carbon::parse($item['activite']['date_proposition'])->format('d/m/Y') }}
                                    </p>
                                    @if(!empty($item['activite']['commentaire']))
                                        <p class="text-xs text-gray-700 mt-1 italic">
                                            "{{ $item['activite']['commentaire'] }}"
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- Notes du marin --}}
            @if($this->notes->isNotEmpty())
                <x-filament::section class="mt-6">
                    <x-slot name="heading">
                        Notes et Observations ({{ $this->notes->count() }})
                    </x-slot>

                    <div class="space-y-4 max-h-64 overflow-y-auto">
                        @foreach($this->notes as $note)
                            <div class="border-l-4 border-blue-500 pl-4 py-3 bg-blue-50 rounded-r">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h5 class="font-medium text-sm text-gray-900">
                                            {{ $note->typeNote?->libelle_court ?? 'Note' }}
                                        </h5>
                                        @if($note->date)
                                            <p class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($note->date)->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right text-xs text-gray-500">
                                        @if($note->redacteur)
                                            <div>{{ $note->redacteur->nom ?? 'Rédacteur inconnu' }}</div>
                                        @endif
                                        @if($note->valide_bcf)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Validé BCF
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700">
                                    {{ $note->libelle }}
                                </p>
                                @if($note->lien_url)
                                    <a href="{{ $note->lien_url }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 block">
                                        Voir le lien associé ↗
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif

            {{-- Répartition détaillée --}}
            <x-filament::section class="mt-6">
                <x-slot name="heading">
                    Répartition Détaillée
                </x-slot>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $this->statistiques['savoirfaires_complets'] }}</div>
                        <div class="text-sm text-green-800">Terminés (100%)</div>
                    </div>
                    
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $this->statistiques['savoirfaires_en_cours'] }}</div>
                        <div class="text-sm text-yellow-800">En cours</div>
                    </div>
                    
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $this->statistiques['savoirfaires_non_commences'] }}</div>
                        <div class="text-sm text-red-800">Non commencés</div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
