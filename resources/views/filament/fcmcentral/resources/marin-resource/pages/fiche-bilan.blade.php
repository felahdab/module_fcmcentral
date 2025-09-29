{{-- @push('styles')
        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.css-fiche-marin')
@endpush --}}

<x-filament-panels::page>

<style>
    /* Variables de couleurs personnalisées */
    :root {
        --success-50: #f0fdf4;
        --success-100: #dcfce7;
        --success-500: #22c55e;
        --success-800: #166534;
       
        --warning-50: #fffbeb;
        --warning-100: #fef3c7;
        --warning-500: #f59e0b;
        --warning-800: #92400e;
       
        --danger-50: #fef2f2;
        --danger-100: #fee2e2;
        --danger-500: #ef4444;
        --danger-800: #991b1b;
    }
    
    /* Classes de couleurs personnalisées */
    .bg-success-100 { background-color: var(--success-100); }
    .bg-success-500 { background-color: var(--success-500); }
    .text-success-800 { color: var(--success-800); }
    .border-success-500 { border-color: var(--success-500); }
    
    .bg-warning-100 { background-color: var(--warning-100); }
    .bg-warning-500 { background-color: var(--warning-500); }
    .text-warning-800 { color: var(--warning-800); }
    .border-warning-500 { border-color: var(--warning-500); }
    
    .bg-danger-100 { background-color: var(--danger-100); }
    .bg-danger-500 { background-color: var(--danger-500); }
    .text-danger-800 { color: var(--danger-800); }
    .border-danger-500 { border-color: var(--danger-500); }
    
    /* Grilles flexibles */
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
        .grid-sections-4 { grid-template-columns: repeat(2, 1fr); }
        .grid-sections-3 { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) {
        .grid-sections-4,
        .grid-sections-3,
        .grid-sections-2 {
            grid-template-columns: 1fr;
        }
    }
    
    /* Animations pour les sections */
    .section-card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .section-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }
    
    /* Styles pour les badges de pourcentage */
    .badge-success {
        background-color: var(--success-100);
        color: var(--success-800);
    }
    
    .badge-warning {
        background-color: var(--warning-100);
        color: var(--warning-800);
    }
    
    .badge-danger {
        background-color: var(--danger-100);
        color: var(--danger-800);
    }
    
    /* Barres de progression améliorées */
    .progress-bar {
        transition: width 0.5s ease-in-out;
        border-radius: 9999px;
    }
    
    .progress-success { background-color: var(--success-500); }
    .progress-warning { background-color: var(--warning-500); }
    .progress-danger { background-color: var(--danger-500); }
    </style>
    


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
            {{-- ✅ STATISTIQUES EN 4 COLONNES --}}
            <div class="grid-sections-4 mb-6">
                <x-filament::section class="text-center section-card">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-success-800">{{ $this->statistiques['pourcentage_global'] }}%</div>
                        <div class="text-sm text-gray-600 mt-1">Progression Globale</div>
                        <div class="text-xs text-gray-500">Savoir-Faire</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center section-card">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-success-800">{{ $this->statistiques['savoirfaires_complets'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">Validés (100%)</div>
                        <div class="text-xs text-gray-500">/ {{ $this->statistiques['total_savoirfaires'] }} total</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center section-card">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-warning-800">{{ $this->statistiques['savoirfaires_en_cours'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">En Cours</div>
                        <div class="text-xs text-gray-500">1% à 99%</div>
                    </div>
                </x-filament::section>

                <x-filament::section class="text-center section-card">
                    <div class="p-4">
                        <div class="text-3xl font-bold text-danger-800">{{ $this->statistiques['savoirfaires_non_commences'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">Non Commencés</div>
                        <div class="text-xs text-gray-500">0%</div>
                    </div>
                </x-filament::section>
            </div>

            {{-- ✅ SECTIONS PRINCIPALES EN 3 COLONNES --}}
            <div class="grid-sections-3 mb-6">
                {{-- SECTION PERSONNEL AVEC DONNÉES DYNAMIQUES --}}
                <x-filament::section class="section-card">
                    <x-slot name="heading">
                        Personnel
                        @can('edit_personnel_info')
                            <x-filament::icon-button
                                icon="heroicon-o-pencil"
                                size="sm"
                                color="gray"
                                tooltip="Modifier"
                                wire:click="$dispatch('open-modal', { id: 'editPersonnel' })"
                                class="ml-2"
                            />
                        @endcan
                    </x-slot>

                    <div class="space-y-3">
                        {{-- Données statiques --}}
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm font-medium text-gray-700">NID:</span>
                            <span class="text-sm text-gray-900">{{ $this->marin->nid }}</span>
                        </div>
                        
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm font-medium text-gray-700">Spécialité:</span>
                            <span class="text-sm text-gray-900">{{ $this->marin->specialite?->libelle_court ?? 'N/A' }}</span>
                        </div>

                        {{-- Données dynamiques --}}
                        {{-- @foreach($this->getDynamicFieldsBySection('Personnel') as $item)
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $item['field']->label }}:</span>
                                <span class="text-sm text-gray-900">{!! $item['formatted_value'] !!}</span>
                            </div>
                        @endforeach --}}
                    </div>
                </x-filament::section>

                {{-- SECTION ÉQUIPE PÉDAGOGIQUE --}}
                <x-filament::section class="section-card">
                    <x-slot name="heading">
                        Équipe Pédagogique
                        @can('edit_team_info')
                            <x-filament::icon-button
                                icon="heroicon-o-pencil"
                                size="sm"
                                color="gray"
                                tooltip="Modifier"
                                wire:click="$dispatch('open-modal', { id: 'editEquipe' })"
                                class="ml-2"
                            />
                        @endcan
                    </x-slot>

                    <div class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm font-medium text-gray-700">Mentor:</span>
                            <span class="text-sm text-gray-900">
                                @if($this->marin->complementsFcm?->mentor)
                                    {{ $this->marin->complementsFcm->mentor->grade?->libelle_court }} {{ $this->marin->complementsFcm->mentor->nom }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>

                        {{-- Données dynamiques --}}
                        {{-- @foreach($this->getDynamicFieldsBySection('Équipe Pédagogique') as $item)
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $item['field']->label }}:</span>
                                <span class="text-sm text-gray-900">{!! $item['formatted_value'] !!}</span>
                            </div>
                        @endforeach --}}
                    </div>
                </x-filament::section>

                {{-- SECTION STATUT --}}
                <x-filament::section class="section-card">
                    <x-slot name="heading">
                        Statut
                    </x-slot>

                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->debut_sedimentation ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm">Début Sédimentation</span>
                            <span class="text-xs text-gray-500">Le: {{ $this->marin->debut_sedimentation ?? 'Sortie BAT' }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->vert_sedimentation ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm">Vert Sédimentation</span>
                            <span class="text-xs text-gray-500">Le: {{ $this->marin->vert_sedimentation ?? 'BCF' }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->vert_competence ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm">Vert Compétence</span>
                            <span class="text-xs text-gray-500">Le: {{ $this->marin->vert_competence ?? 'BCF' }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->selection_bs ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm">Sélection BS</span>
                            <span class="text-xs text-gray-500">Le: {{ $this->marin->selection_bs ?? 'BCF' }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" {{ $this->marin->validation_exa ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm">Validation EXA</span>
                            <span class="text-xs text-gray-500">Le: {{ $this->marin->validation_exa ?? 'FPS' }}</span>
                        </div>
                    </div>

                    {{-- Durées --}}
                    <div class="mt-4 pt-4 border-t space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium">Durée sédimentation:</span>
                            <span>{{ $this->calculerDureeSedimentation() }} jours</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium">Durée du PPA:</span>
                            <span>{{ $this->calculerDureePPA() }} jours</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium">Durée Totale:</span>
                            <span>{{ $this->calculerDureeTotale() }} jours</span>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            {{-- ✅ SECTIONS EN 2 COLONNES --}}
            <div class="grid-sections-2 mb-6">
                {{-- PROGRESSION PAR SAVOIR-FAIRE --}}
                <x-filament::section class="section-card">
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

                                        <span class="text-xs px-2 py-1 rounded-full {{ $savoirFaire['couleur_classe']['bg'] }} {{ $savoirFaire['couleur_classe']['text'] }}">
                                            {{ $savoirFaire['pourcentage'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-1">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full progress-bar {{ $savoirFaire['couleur_classe']['progress'] }}"
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

                {{-- NOTES ET OBSERVATIONS --}}
                <x-filament::section class="section-card">
                    <x-slot name="heading">
                        Notes et Observations ({{ $this->notes->count() }})
                        <x-filament::icon-button
                            icon="heroicon-o-plus"
                            size="sm"
                            color="primary"
                            tooltip="Ajouter une note"
                            wire:click="ajouterNote"
                            class="ml-2"
                        />
                    </x-slot>

                    @if($this->notes->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <p>Aucune note enregistrée</p>
                        </div>
                    @else
                        <div class="space-y-4 max-h-96 overflow-y-auto">
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
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800">
                                                    Validé BCF
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $note->libelle }}</p>
                                    @if($note->lien_url)
                                        <a href="{{ $note->lien_url }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 block">
                                            Voir le lien associé ↗
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- ✅ INDICATEURS EN PLEINE LARGEUR --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">
                    Indicateurs
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">Date dernière MAJ FFAST:</span>
                            <span class="text-sm font-bold">{{ $this->getDateDerniereMajFfast() }}</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">% Compagnonnage:</span>
                            <span class="text-sm font-bold">{{ $this->getPourcentageCompagnonnage() }}%</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">% Stages:</span>
                            <span class="text-sm font-bold">{{ $this->getPourcentageStages() }}%</span>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- ✅ SECTIONS DYNAMIQUES SUPPLÉMENTAIRES --}}
            {{-- @foreach(['Évaluation', 'Compétences', 'Formations'] as $sectionName)
                @if($this->sectionHasData($sectionName))
                    <x-filament::section class="mt-6 section-card">
                        <x-slot name="heading">
                            {{ $sectionName }}
                            @can('edit_dynamic_fields')
                                <x-filament::icon-button
                                    icon="heroicon-o-pencil"
                                    size="sm"
                                    color="gray"
                                    tooltip="Modifier"
                                    wire:click="$dispatch('open-modal', { id: 'editDynamicFields' })"
                                    class="ml-2"
                                />
                            @endcan
                        </x-slot>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($this->getDynamicFieldsBySection($sectionName) as $item)
                                @if(!empty($item['value']))
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $item['field']->label }}</h4>
                                        
                                        @if($item['field']->field_type === 'textarea')
                                            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $item['value'] }}</div>
                                        @elseif($item['field']->field_type === 'checkbox')
                                            <div class="flex flex-wrap gap-1">
                                                @if(is_array($item['value']))
                                                    @foreach($item['value'] as $checkValue)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $checkValue }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-sm text-gray-900">{{ $item['formatted_value'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-900">{!! $item['formatted_value'] !!}</div>
                                        @endif

                                        @if($item['field']->help_text)
                                            <p class="text-xs text-gray-500 mt-1 italic">{{ $item['field']->help_text }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-filament::section>
                @endif
            @endforeach --}}

        @endif
    @endif

    {{-- ✅ MODALS POUR L'ÉDITION --}}
    <x-filament::modal id="editDynamicFields" width="4xl">
        <x-slot name="heading">
            Modifier les informations complémentaires
        </x-slot>

        @if(isset($this->bilan))
            @livewire('dynamic-form', ['bilan' => $this->bilan], key('dynamic-form-' . $this->bilan->id))
        @endif
    </x-filament::modal>

    <x-filament::modal id="ajouterNote" width="2xl">
        <x-slot name="heading">
            Ajouter une note
        </x-slot>

        @livewire('Modules\FcmCentral\Http\Livewire\AddNoteForm', ['marinId' => $this->marinId], key('add-note-' . $this->marinId))
    </x-filament::modal>
</x-filament-panels::page>

