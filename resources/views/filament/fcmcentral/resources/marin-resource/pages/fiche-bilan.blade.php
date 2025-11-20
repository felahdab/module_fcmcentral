<x-filament-panels::page>

<style>
    .grid-sections-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .grid-sections-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .grid-sections-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .grid-sections-2,
        .grid-sections-3,
        .grid-sections-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .grid-sections-2,
        .grid-sections-3,
        .grid-sections-4 {
            grid-template-columns: 1fr;
        }
    }

    .section-card {
        transition: all 0.3s ease;
    }

    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .progress-bar {
        transition: width 0.5s ease-in-out;
    }

    .progress-success {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .progress-warning {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .progress-danger {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .progress-gray {
        background: linear-gradient(90deg, #9ca3af 0%, #6b7280 100%);
    }

    .note-card {
        transition: all 0.2s ease;
    }

    .note-card:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .section-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

{{-- VÉRIFICATION MARIN --}}
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

    {{-- EN-TÊTE --}}
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
                            <strong>Mentor:</strong>
                            {{ $this->marin->complementsFcm->mentor->grade?->libelle_court }}
                            {{ $this->marin->complementsFcm->mentor->prenom }}
                            {{ $this->marin->complementsFcm->mentor->nom }}
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

    {{-- VÉRIFICATION PARCOURS --}}
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

        {{-- STATISTIQUES (4 COLONNES) --}}
        <div class="grid-sections-4 mb-6">
            <x-filament::section class="text-center section-card">
                <div class="p-4">
                    <div class="text-3xl font-bold {{ $this->statistiques['pourcentage_global'] >= 80 ? 'text-success-800' : ($this->statistiques['pourcentage_global'] >= 50 ? 'text-warning-800' : 'text-danger-800') }}">
                        {{ $this->statistiques['pourcentage_global'] }}%
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Progression Globale</div>
                    <div class="text-xs text-gray-500">Savoir-Faire</div>
                </div>
            </x-filament::section>

            <x-filament::section class="text-center section-card">
                <div class="p-4">
                    <div class="text-3xl font-bold text-success-800">
                        {{ $this->statistiques['savoirfaires_valides'] }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Validés</div>
                    <div class="text-xs text-gray-500">/ {{ $this->statistiques['total_savoirfaires'] }} total</div>
                </div>
            </x-filament::section>

            <x-filament::section class="text-center section-card">
                <div class="p-4">
                    <div class="text-3xl font-bold text-warning-800">
                        {{ $this->statistiques['savoirfaires_en_cours'] }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">En Cours</div>
                    <div class="text-xs text-gray-500">Partiellement complétés</div>
                </div>
            </x-filament::section>

            <x-filament::section class="text-center section-card">
                <div class="p-4">
                    <div class="text-3xl font-bold text-danger-800">
                        {{ $this->statistiques['savoirfaires_non_commences'] }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Non Commencés</div>
                    <div class="text-xs text-gray-500">0% de progression</div>
                </div>
            </x-filament::section>
        </div>

        {{-- SECTIONS PRINCIPALES (3 COLONNES) --}}
        <div class="grid-sections-3 mb-6">
            
            {{-- PERSONNEL --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">Personnel</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">NID:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->nid }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Grade:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->grade?->libelle_court ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Spécialité:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->specialite?->libelle_court ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Unité:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->unite?->libelle_court ?? 'N/A' }}</span>
                    </div>
                </div>
            </x-filament::section>

            {{-- ÉQUIPE PÉDAGOGIQUE --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">Équipe Pédagogique</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Cohorte:</span>
                        <span class="text-sm text-gray-900">
                            {{ $this->marin->complementsFcm?->cohorte?->libelle_court ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Mentor:</span>
                        <span class="text-sm text-gray-900">
                            @if($this->marin->complementsFcm?->mentor)
                                {{ $this->marin->complementsFcm->mentor->grade?->libelle_court }}
                                {{ $this->marin->complementsFcm->mentor->nom }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </x-filament::section>

            {{-- STATUT --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">Statut & Jalons</x-slot>
                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 border-b">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->debut_sedimentation ? 'checked' : '' }} disabled class="rounded text-blue-600">
                            <span class="text-sm font-medium">Début Sédimentation</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $this->marin->debut_sedimentation ? \Carbon\Carbon::parse($this->marin->debut_sedimentation)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->vert_sedimentation ? 'checked' : '' }} disabled class="rounded text-green-600">
                            <span class="text-sm font-medium">Vert Sédimentation</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $this->marin->vert_sedimentation ? \Carbon\Carbon::parse($this->marin->vert_sedimentation)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->vert_competence ? 'checked' : '' }} disabled class="rounded text-green-600">
                            <span class="text-sm font-medium">Vert Compétence</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $this->marin->vert_competence ? \Carbon\Carbon::parse($this->marin->vert_competence)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    {{-- <div class="flex items-center justify-between py-2 border-b">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->selection_bs ? 'checked' : '' }} disabled class="rounded text-blue-600">
                            <span class="text-sm font-medium">Sélection BS</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $this->marin->selection_bs ? \Carbon\Carbon::parse($this->marin->selection_bs)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->validation_exa ? 'checked' : '' }} disabled class="rounded text-purple-600">
                            <span class="text-sm font-medium">Validation EXA</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $this->marin->validation_exa ? \Carbon\Carbon::parse($this->marin->validation_exa)->format('d/m/Y') : 'N/A' }}
                        </span>
                    </div> --}}
                </div>
                <div class="mt-4 pt-4 border-t space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Durée sédimentation:</span>
                        <span class="text-gray-900">{{ $this->calculerDureeSedimentation() }} jours</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Durée du PPA:</span>
                        <span class="text-gray-900">{{ $this->calculerDureePPA() }} jours</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-700">Durée Totale:</span>
                        <span class="font-bold text-blue-600">{{ $this->calculerDureeTotale() }} jours</span>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- SECTIONS 2 COLONNES --}}
        <div class="grid-sections-2 mb-6">
            
            {{-- SAVOIR-FAIRE --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">
                    Progression par Savoir-Faire ({{ $this->savoirFaires->count() }})
                </x-slot>
                <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar">
                    @forelse($this->savoirFaires->groupBy('affectation') as $affectation => $savoirFairesByAffectation)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-semibold text-sm text-gray-800 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                </svg>
                                {{ $affectation }}
                            </h4>
                            @foreach($savoirFairesByAffectation as $savoirFaire)
                                <div class="mb-3 pb-3 border-b border-gray-200 last:border-b-0 bg-white p-3 rounded">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <h5 class="text-xs font-medium text-gray-900">
                                                {{ $savoirFaire['libelle_court'] }}
                                            </h5>
                                            <p class="text-xs text-gray-500 mt-1">{{ $savoirFaire['competence'] }}</p>
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
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full progress-bar {{ str_replace('bg-', 'progress-', $savoirFaire['couleur_classe']['progress']) }}"
                                                style="width: {{ $savoirFaire['pourcentage'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center text-xs text-gray-600">
                                        <span>
                                            <strong>{{ $savoirFaire['objectifs_valides'] }}</strong> / {{ $savoirFaire['total_objectifs'] }} objectifs
                                        </span>
                                        @if($savoirFaire['etat_valid'] && $savoirFaire['date_validation'])
                                            <span class="text-green-600 font-medium">
                                                ✓ {{ \Carbon\Carbon::parse($savoirFaire['date_validation'])->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                            </svg>
                            <p>Aucun savoir-faire dans ce parcours</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- NOTES --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <span>Notes et Observations ({{ $this->notes->count() }})</span>
                        @if($this->canCreateNote())
                            <x-filament::icon-button
                                icon="heroicon-o-plus-circle"
                                size="md"
                                color="primary"
                                tooltip="Ajouter une note"
                                wire:click="ajouterNote"
                            />
                        @endif
                    </div>
                </x-slot>
                @if($this->notes->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm">Aucune note enregistrée</p>
                        @if($this->canCreateNote())
                            <button wire:click="ajouterNote" class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ajouter la première note
                            </button>
                        @endif
                    </div>
                @else
                    <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
                        @foreach($this->notes as $note)
                            <div class="border-l-4 {{ $note->valide_bcf ? 'border-green-500 bg-green-50' : 'border-blue-500 bg-blue-50' }} pl-4 py-3 rounded-r note-card">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h5 class="font-medium text-sm text-gray-900">
                                            {{ $note->typeNote?->libelle_court ?? 'Note' }}
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
                                                {{ $note->redacteur->grade?->libelle_court ?? '' }} {{ $note->redacteur->nom ?? 'Inconnu' }}
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
        </div>

        {{-- OBJECTIFS EN ATTENTE --}}
        @if($this->activitesEnAttente->count() > 0)
            <x-filament::section class="section-card mb-6">
                <x-slot name="heading">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        Objectifs en Attente de Validation ({{ $this->activitesEnAttente->count() }})
                    </div>
                </x-slot>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->activitesEnAttente as $item)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h6 class="text-sm font-medium text-gray-900">
                                        {{ $item['objectif']['libelle_court'] ?? 'Objectif' }}
                                    </h6>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <strong>SF:</strong> {{ $item['savoirfaire'] }}<br>
                                        <strong>Comp:</strong> {{ $item['competence'] }}<br>
                                        <strong>Aff:</strong> {{ $item['affectation'] }}
                                    </p>
                                    @if(!empty($item['objectif']['date_proposition']))
                                        <p class="text-xs text-orange-600 font-medium mt-2">
                                            Proposé le {{ \Carbon\Carbon::parse($item['objectif']['date_proposition'])->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- INDICATEURS --}}
        <x-filament::section class="section-card">
            <x-slot name="heading">Indicateurs Complémentaires</x-slot>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-blue-600 font-medium mb-1">Dernière MAJ FFAST</div>
                            <div class="text-lg font-bold text-blue-900">{{ $this->getDateDerniereMajFfast() }}</div>
                        </div>
                        <svg class="w-10 h-10 text-blue-400 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-purple-600 font-medium mb-1">% Compagnonnage</div>
                            <div class="text-lg font-bold text-purple-900">{{ $this->getPourcentageCompagnonnage() }}%</div>
                        </div>
                        <svg class="w-10 h-10 text-purple-400 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-green-600 font-medium mb-1">% Stages</div>
                            <div class="text-lg font-bold text-green-900">{{ $this->getPourcentageStages() }}%</div>
                        </div>
                        <svg class="w-10 h-10 text-green-400 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </x-filament::section>

    @endif
@endif

{{-- MODAL AJOUT NOTE --}}
<x-filament::modal id="ajouterNote" width="2xl">
    <x-slot name="heading">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            Ajouter une note
        </div>
    </x-slot>

    @livewire('Modules\FcmCentral\Http\Livewire\AddNoteForm', ['marinId' => $marinId], key('add-note-' . $marinId))
</x-filament::modal>

</x-filament-panels::page>
