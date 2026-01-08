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

    /* ✅ DARK MODE COMPATIBLE */
    .dark .section-card:hover {
        box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.1), 0 2px 4px -1px rgba(255, 255, 255, 0.06);
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

    /* ✅ DARK MODE POUR NOTE CARD */
    .dark .note-card:hover {
        box-shadow: 0 2px 4px rgba(255, 255, 255, 0.1);
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

    /* ✅ DARK MODE SCROLLBAR */
    .dark .custom-scrollbar::-webkit-scrollbar-track {
        background: #1e293b;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* ✅ AMÉLIORATION PROGRESSION SAVOIR-FAIRE EN DARK MODE */
    .sf-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .dark .sf-item {
        background: #1e293b;
        border-color: #334155;
    }

    .sf-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .dark .sf-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        background: #334155;
    }

    .sf-group-header {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .dark .sf-group-header {
        background: #0f172a;
        border-color: #1e293b;
    }

    /* ✅ AMÉLIORATION BARRE DE PROGRESSION */
    .progress-bg {
        background: #e5e7eb;
    }

    .dark .progress-bg {
        background: #374151;
    }

    /* ✅ TEXTES LISIBLES EN DARK MODE */
    .text-adaptive-gray {
        color: #4b5563;
    }

    .dark .text-adaptive-gray {
        color: #9ca3af;
    }

    .text-adaptive-dark {
        color: #111827;
    }

    .dark .text-adaptive-dark {
        color: #f9fafb;
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

    /* Blanc */ 
    .text-white { color: #rgb(255, 255, 255) }
    .text-dark { color: #rgb(0, 0, 0) }
    .text-gray { color:#94a3b8;) }

    /* Vert */
.text-green-600 { color: #16a34a; }
.text-green-800 { color: #166534; }
.text-green-400 { color :#6be6bd;}
.bg-green-50 { background-color: #f0fdf4; }
.bg-green-100 { background-color: #dcfce7; }
.border-green-200 { border-color: #bbf7d0; }
.dark .text-green-600, .dark .text-green-800 { color: #bbf7d0; }
.dark .bg-green-50, .dark .bg-green-100 { background-color: #052e16; }
.dark .border-green-200 { border-color: #166534; }

/* Bleu */
.text-blue-600 { color: #2563eb; }
.text-blue-800 { color: #1e40af; }
.text-blue-400 { color: #4065df; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-blue-100 { background-color: #dbeafe; }
.border-blue-200 { border-color: #bfdbfe; }
.dark .text-blue-600, .dark .text-blue-800 { color: #bfdbfe; }
.dark .bg-blue-50, .dark .bg-blue-100 { background-color: #1e293b; }
.dark .border-blue-200 { border-color: #1e40af; }

/* Violet */
.text-purple-600 { color: #9333ea; }
.text-purple-800 { color: #581c87; }
.text-purple-400 { color: #8f43ca; }
.bg-purple-50 { background-color: #faf5ff; }
.bg-purple-100 { background-color: #f3e8ff; }
.border-purple-200 { border-color: #e9d5ff; }
.dark .text-purple-600, .dark .text-purple-900 { color: #e9d5ff; }
.dark .bg-purple-50, .dark .bg-purple-100 { background-color: #2e1065; }
.dark .border-purple-200 { border-color: #7c3aed; }

/* Orange */
.text-orange-600 { color: #ea580c; }
.text-orange-800 { color: #bb4b0f; }
.text-orange-400 { color: #f0844a; }
.bg-orange-50 { background-color: #fff7ed; }
.bg-orange-100 { background-color: #ffedd5; }
.border-orange-200 { border-color: #fed7aa; }
.dark .text-orange-600 { color: #fed7aa; }
.dark .bg-orange-50, .dark .bg-orange-100 { background-color: #7c2d12; }
.dark .border-orange-200 { border-color: #ea580c; }

/* Rouge */
.text-red-600 { color: #dc2626; }
.bg-red-100 { background-color: #fee2e2; }
.border-red-500 { border-color: #ef4444; }
.dark .text-red-600 { color: #fee2e2; }
.dark .bg-red-100 { background-color: #7f1d1d; }
.dark .border-red-500 { border-color: #fee2e2; }

/* Bleu */
.bg-gradient-blue {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}
.dark .bg-gradient-blue {
    background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
}

/* Vert */
.bg-gradient-green {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}
.dark .bg-gradient-green {
    background: linear-gradient(135deg, #052e16 0%, #22c55e 100%);
}

/* Violet */
.bg-gradient-purple {
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
}
.dark .bg-gradient-purple {
    background: linear-gradient(135deg, #2e1065 0%, #a78bfa 100%);
}

/* Orange */
.bg-gradient-orange {
    background: linear-gradient(135deg, #f1eacb 0%, #fa8211 100%);
}
.dark .bg-gradient-orange {
    background: linear-gradient(135deg, #654010 0%, #f77d0b 100%);
}

.text-5xl{
    font-size: 40px;
}

.text-3xl{
    font-size: 30px;
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
        <x-filament::section>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        {{ $this->marin->grade?->libelle_court }} {{ $this->marin->prenom }} {{ $this->marin->nom }}
                    </h1>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm opacity-90">
                        <div><strong>Matricule:</strong> {{ $this->marin->matricule }}</div>
                        <div><strong>Spécialité:</strong> {{ $this->marin->specialite?->libelle_long ?? 'N/A' }}</div>
                        <div><strong>Unité:</strong> {{ $this->marin->unite?->libelle_long ?? 'N/A' }}</div>
                        <div><strong>Cohorte:</strong> {{ $this->marin->complementsFcm?->cohorte?->libelle_long ?? 'N/A' }}</div>
                    </div>
                    @if($this->marin->complementsFcm?->mentor)
                        <div class="mt-2 text-sm opacity-90">
                            <strong>Mentor:</strong>
                            {{ $this->marin->complementsFcm->mentor->grade?->libelle_long }}
                            {{ $this->marin->complementsFcm->mentor->prenom }}
                            {{ $this->marin->complementsFcm->mentor->nom }}
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-sm opacity-90">Fiche générée le</div>
                    <div class="text-lg font-semibold">{{ now()->format('d/m/Y à H:i') }}</div>
                    <div class="text-sm opacity-90">Dernière MAJ FFAST</div>
                    <div class="text-lg font-semibold">{{ $this->getDateDerniereMajFfast() }}</div>
                </div>
            </div>
        </x-filament::section>
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

      {{-- STATISTIQUES (DYNAMIQUE : 2 à 4 COLONNES) --}}
<div class="grid {{ $this->hasMultipleNonZeroStats() ? 'grid-sections-4' : 'grid-sections-2' }} gap-4 mb-6">

    {{-- PROGRESSION GLOBALE --}}
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">Progression Globale</x-slot>
        <div class="p-4">
            <div class="text-5xl font-bold
                {{ $this->statistiques['pourcentage_global'] >= 80 ? 'text-green-600' : 
                   ($this->statistiques['pourcentage_global'] >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
                {{ $this->statistiques['pourcentage_global'] }}%
            </div>
        </div>
    </x-filament::section>

    {{-- SAVOIR-FAIRE VALIDÉS --}}
    @if($this->statistiques['savoirfaires_valides'] > 0)
        <x-filament::section class="text-center section-card">
            <x-slot name="heading">Savoir-faire validés</x-slot>
            <div class="p-4">
                @php
                    $total = $this->statistiques['total_savoirfaires'] ?? 0;
                    $valide = $this->statistiques['savoirfaires_valides'] ?? 0;
                    $pourcentage = $total > 0 ? round(($valide / $total) * 100, 0) : 0;
                @endphp
                <div class="text-5xl font-bold text-green-600">
                    {{ $pourcentage }}%
                </div>
                <div class="text-xs text-gray-600 mt-2">
                    {{ $valide }} / {{ $total }} total
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- SAVOIR-FAIRE EN COURS --}}
    @if($this->statistiques['savoirfaires_en_cours'] > 0)
        <x-filament::section class="text-center section-card">
            <x-slot name="heading">Savoir-faire en cours</x-slot>
            <div class="p-4">
                @php
                    $total = $this->statistiques['total_savoirfaires'] ?? 0;
                    $enCours = $this->statistiques['savoirfaires_en_cours'] ?? 0;
                    $pourcentage = $total > 0 ? round(($enCours / $total) * 100, 0) : 0;
                @endphp
                <div class="text-5xl font-bold text-blue-600">
                    {{ $pourcentage }}%
                </div>
                <div class="text-xs text-gray-600 mt-2">
                    {{ $enCours }} / {{ $total }} total
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- SAVOIR-FAIRE NON COMMENCÉS --}}
    @if($this->statistiques['savoirfaires_non_commences'] > 0)
        <x-filament::section class="text-center section-card">
            <x-slot name="heading">Savoir-faire Non Commencés</x-slot>
            <div class="p-4">
                @php
                    $total = $this->statistiques['total_savoirfaires'] ?? 0;
                    $enCours = $this->statistiques['savoirfaires_en_cours'] ?? 0;
                    $pourcentage = $total > 0 ? round(($enCours / $total) * 100, 0) : 0;
                @endphp
                <div class="text-5xl font-bold text-purple-600">
                    {{ $this->statistiques['savoirfaires_non_commences'] }}
                </div>
                <div class="text-xs text-gray-600 mt-2">
                    sur {{ $this->statistiques['total_savoirfaires'] }} total
                </div>
            </div>
        </x-filament::section>
    @endif
</div>

{{-- INDICATEURS PAR TYPE (COMPAGNONNAGE / STAGE / EAD) --}}
<div class="grid grid-sections-3 gap-4 mb-6">

    {{-- COMPAGNONNAGE --}}
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">SF - Compagnonnage</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['total_compagnonnage'] ?? 0;
                $valides = $this->statistiques['compagnonnage_valides'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;
            @endphp
            <div class="text-3xl font-bold 
                {{ $pourcentage >= 80 ? 'text-green-600' : 
                   ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
                {{ $pourcentage }}%
            </div>
            <div class="text-sm text-gray-600 mt-2">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>

    {{-- STAGE --}}
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">SF - Stage</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['total_stages'] ?? 0;
                $valides = $this->statistiques['stages_valides'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;
            @endphp
            <div class="text-3xl font-bold 
                {{ $pourcentage >= 80 ? 'text-green-600' : 
                   ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
                {{ $pourcentage }}%
            </div>
            <div class="text-sm text-gray-600 mt-2">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>

    {{-- ÉCOLE À DISTANCE --}}
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">SF - École à Distance</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['total_ead'] ?? 0;
                $valides = $this->statistiques['ead_valides'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;
            @endphp
            <div class="text-3xl font-bold 
                {{ $pourcentage >= 80 ? 'text-green-600' : 
                   ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
                {{ $pourcentage }}%
            </div>
            <div class="text-sm text-gray-600 mt-2">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>
</div>

{{-- INDICATEURS PAR COMPÉTENCE (OPTIONNEL) --}}
@if($this->statistiques['competences_stages_total'] > 0 || 
    $this->statistiques['competences_compagnonnage_total'] > 0 || 
    $this->statistiques['competences_ead_total'] > 0)
<div class="grid grid-sections-3 gap-4 mb-6">

    {{-- COMPÉTENCES COMPAGNONNAGE --}}
    @if($this->statistiques['competences_compagnonnage_total'] > 0)
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">C Compagnonnage</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['competences_compagnonnage_total'] ?? 0;
                $valides = $this->statistiques['competences_compagnonnage_valides'] ?? 0;
                $enCours = $this->statistiques['competences_compagnonnage_en_cours'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;

            @endphp
             <div class="text-3xl font-bold 
             {{ $pourcentage >= 80 ? 'text-green-600' : 
                ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
             {{ $pourcentage }}%
         </div>
            <div class="text-sm text-gray-600 mt-1">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>
    @endif

    {{-- COMPÉTENCES STAGE --}}
    @if($this->statistiques['competences_stages_total'] > 0)
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">C Stage</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['competences_stages_total'] ?? 0;
                $valides = $this->statistiques['competences_stages_valides'] ?? 0;
                $enCours = $this->statistiques['competences_stages_en_cours'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;
            @endphp
            <div class="text-3xl font-bold 
            {{ $pourcentage >= 80 ? 'text-green-600' : 
               ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
            {{ $pourcentage }}%
        </div>
            <div class="text-sm text-gray-600 mt-1">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>
    @endif

    {{-- COMPÉTENCES EAD --}}
    @if($this->statistiques['competences_ead_total'] > 0)
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">C EAD</x-slot>
        <div class="p-4">
            @php
                $total = $this->statistiques['competences_ead_total'] ?? 0;
                $valides = $this->statistiques['competences_ead_valides'] ?? 0;
                $enCours = $this->statistiques['competences_ead_en_cours'] ?? 0;
                $pourcentage = $total > 0 ? round(($valides / $total) * 100, 0) : 0;
            @endphp
           <div class="text-3xl font-bold 
           {{ $pourcentage >= 80 ? 'text-green-600' : 
              ($pourcentage >= 50 ? 'text-blue-600' : 'text-orange-600') }}">
           {{ $pourcentage }}%
       </div>
            <div class="text-sm text-gray-600 mt-1">
                {{ $valides }} effectué{{ $valides > 1 ? 's' : '' }} / {{ $total }} total
            </div>
        </div>
    </x-filament::section>
    @endif
</div>
@endif


  {{-- <x-filament::section class="section-card">
    <x-slot name="heading">Indicateurs Complémentaires</x-slot>
    <div class="grid grid-cols-1 md:grid-cols-3  gap-6">
        <div class="bg-gradient-blue p-4 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-blue-600 font-medium mb-1">Compagnonnage</div>
                    <div class="text-lg font-bold text-blue-900">{{ $this->statistiques['compagnonnage_valides'] }} effectué
                        @php $this->statistiques['compagnonnage_valides']>1?'s':'' @endphp / {{ $this->statistiques['total_compagnonnage'] }} total</div>
                    <div class="text-lg font-bold text-blue-900">{{ $this->getPourcentageCompagnonnage() }}%</div>
                </div>
                <svg class="w-10 h-10 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        <div class="bg-gradient-purple p-4 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-purple-600 font-medium mb-1">Stages</div>
                    <div class="text-lg font-bold text-purple-900">{{ $this->statistiques['stages_valides'] }} effectué
                        @php $this->statistiques['compagnonnage_valides']>1?'s':'' @endphp  / {{ $this->statistiques['total_stages'] }} total</div>
                    <div class="text-lg font-bold text-purple-900">{{ $this->getPourcentageStages() }}%</div>  
                </div>
                <svg class="w-10 h-10 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
            </div>
        </div>
        <div class="bg-gradient-green p-4 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-green-600 font-medium mb-1">Ecole a Distance</div>
                    <div class="text-lg font-bold text-green-900">{{ $this->statistiques['ead_valides'] }} effectué
                        @php $this->statistiques['compagnonnage_valides']>1?'s':'' @endphp  / {{ $this->statistiques['total_ead'] }} total</div>
                    <div class="text-lg font-bold text-green-900">{{ $this->getPourcentageEad() }}%</div>
                </div>
                <svg class="w-10 h-10 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                </svg>
            </div>
        </div>
       
    </div>
</x-filament::section> --}}


        {{-- SECTIONS PRINCIPALES (3 COLONNES) --}}
        <div class="grid-sections-3 mb-6">
            
            {{-- PERSONNEL --}}
            <x-filament::section class="section-card">
                <x-slot name="heading">Personnel</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Matricule:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->matricule }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Grade:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->grade?->libelle_long ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Spécialité:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->specialite?->libelle_long ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Unité:</span>
                        <span class="text-sm text-gray-900">{{ $this->marin->unite?->libelle_long ?? 'N/A' }}</span>
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
                            {{ $this->marin->complementsFcm?->cohorte?->libelle_long ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-sm font-medium text-gray-700">Mentor:</span>
                        <span class="text-sm text-gray-900">
                            @if($this->marin->complementsFcm?->mentor)
                                {{ $this->marin->complementsFcm->mentor->grade?->libelle_long }}
                                {{ $this->marin->complementsFcm->mentor->nom }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </x-filament::section>

            {{-- STATUT --}}
            {{-- ✅ ÉTATS (BASÉ SUR LE MODÈLE ETAT) --}}
{{-- <x-filament::section class="section-card">
    <x-slot name="heading">Statut & Jalons</x-slot>
    <div class="space-y-3">
        <div class="flex justify-between items-center border-b pb-2">
            <span class="text-sm font-medium text-gray-700">Statut:</span>
            <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $this->getStatutBgColor() }} {{ $this->getStatutColor() }}">
                {{ $this->getStatutLibelle() }}
            </span>
        </div>
       
        @if($this->getDateDebutEtat())
            <div class="flex justify-between border-b pb-2">
                <span class="text-sm font-medium text-gray-700">Date Début:</span>
                <span class="text-sm text-gray-900">
                    {{ $this->getDateDebutEtat() }}
                </span>
            </div>
        @endif
       
        @if($this->getDateFinEtat())
            <div class="flex justify-between border-b pb-2">
                <span class="text-sm font-medium text-gray-700">Date Fin:</span>
                <span class="text-sm text-gray-900">
                    {{ $this->getDateFinEtat() }}
                </span>
            </div>
        @endif
       
        @if($this->getDureeEtat() > 0)
            <div class="flex justify-between pt-2">
                <span class="text-sm font-medium text-gray-700">Durée:</span>
                <span class="text-sm font-bold text-blue-600">
                    {{ round($this->getDureeEtat()) }} jours
                </span>
            </div>
        @endif
       
        @if($this->etatActuel?->date_fin)
            <div class="mt-2 pt-2 border-t">
                <span class="text-xs text-gray-500 italic">
                    ✓ État terminé
                </span>
            </div>
        @else
            <div class="mt-2 pt-2 border-t">
                <span class="text-xs text-green-600 font-medium">
                    ● En cours
                </span>
            </div>
        @endif
    </div>
</x-filament::section> --}}

{{-- HISTORIQUE DES ÉTATS --}}
<x-filament::section class="section-card">
    <x-slot name="heading">
        <div class="flex items-center justify-between">
            <span>Jalons FCM</span>
            @if($this->canEditJalons())
                <x-filament::icon-button
                    icon="heroicon-o-pencil"
                    size="md"
                    color="warning"
                    tooltip="Modifier les jalons"
                    wire:click="ouvrirJalonModal"
                />
            @endif

            {{-- <span>Statut & Jalons</span>
            @if($this->canCreateEtat())
                <x-filament::icon-button
                    icon="heroicon-o-plus-circle"
                    size="md"
                    color="primary"
                    tooltip="Ajouter un Statut/Jalon"
                    wire:click="ajouterEtat"
                />
            @endif --}}
        </div>
    </x-slot>

    <div class="space-y-3">
        {{-- DÉBUT SÉDIMENTATION --}}
        <div class="flex items-center justify-between p-2 rounded bg-blue-50 border border-blue-200">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-arrow-path" class="w-5 h-5 text-blue-600" />
                <span class="text-sm font-medium text-gray-900">Début Sédimentation</span>
            </div>
            <span class="text-sm text-gray-700">
                {{ $this->getDateSedimentationFormatee() }}
            </span>
        </div>

        {{-- VERT FCM --}}
        <div class="flex items-center justify-between p-2 rounded bg-green-50 border border-green-200">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-600" />
                <span class="text-sm font-medium text-gray-900">Vert FCM</span>
            </div>
            <span class="text-sm text-gray-700">
                {{ $this->getDateFcmFormatee() }}
            </span>
        </div>

        {{-- VERT COMPÉTENCE --}}
        <div class="flex items-center justify-between p-2 rounded bg-purple-50 border border-purple-200">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5 text-purple-600" />
                <span class="text-sm font-medium text-gray-900">Vert Compétence</span>
            </div>
            <span class="text-sm text-gray-700">
                {{ $this->getDateCompetenceFormatee() }}
            </span>
        </div>
    </div>

   
    @if($this->historiqueEtats->isEmpty())
        <div class="text-center py-4 text-gray-500 text-sm">
            Aucun jalon enregistré
        </div>
    @else
        <div class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
            @foreach($this->historiqueEtats as $etat)
                <div class="border-l-4 pl-3 py-2 rounded-r  transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $etat->typeEtat->libelle_court }}
                            </span>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                📅 Du {{ \Carbon\Carbon::parse($etat->date_debut)->format('d/m/Y') }}
                                @if($etat->date_fin)
                                    au {{ \Carbon\Carbon::parse($etat->date_fin)->format('d/m/Y') }}
                                @else
                                    <span class="text-green-600 font-medium">(en cours)</span>
                                @endif
                            </div>
                        </div>
                       
                        <div class="flex items-center gap-2">
                            @if($etat->date_debut && ($etat->date_fin || !$etat->date_fin))
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-200 text-gray-700  ">
                                    {{ round(\Carbon\Carbon::parse($etat->date_debut)->diffInDays($etat->date_fin ? \Carbon\Carbon::parse($etat->date_fin) : now())) }} j
                                </span>
                            @endif
                           
                            {{-- ✅ BOUTON MODIFIER --}}
                            @if($this->canCreateEtat())
                                <x-filament::icon-button
                                    icon="heroicon-o-pencil"
                                    size="sm"
                                    color="warning"
                                    tooltip="Modifier cet état"
                                    wire:click="modifierEtat({{ $etat->id }})"
                                />
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
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
                        <div class="sf-group-header">
                            <h4 class="font-semibold text-sm text-adaptive-dark flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                </svg>
                                {{ $affectation }}
                            </h4>
                        </div>
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
                            </div>
                        @endforeach
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
            
            {{-- NOTES --}}
           <x-filament::section class="section-card">
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <span>Commentaires et Observations ({{ $this->notes->count() }})</span>
                        @if($this->canCreateNote())
                            <x-filament::icon-button
                                icon="heroicon-o-plus-circle"
                                size="md"
                                color="primary"
                                tooltip="Ajouter un commentaire"
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
                        <p class="text-sm">Aucun commentaire enregistré</p>
                        @if($this->canCreateNote())
                            <button wire:click="ajouterNote" class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ajouter un commentaire
                            </button>
                        @endif
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


{{-- MODAL ETAT --}}
<x-filament::modal
    id="ajouterEtat"
    width="2xl"
    :close-by-clicking-away="false"
>
    <x-slot name="heading">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            <span>Jalon/Statut FCM</span>
        </div>
    </x-slot>

    @livewire('Modules\FcmCentral\Http\Livewire\AddEtatForm', ['marinId' => $marinId], key('add-etat-form-' . $marinId))
</x-filament::modal>



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
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date Sédimentation</label>
            <input 
                type="date" 
                wire:model="jalonForm.date_sedimentation"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date FCM</label>
            <input 
                type="date" 
                wire:model="jalonForm.date_fcm"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600"
            />
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date Compétence</label>
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



</x-filament-panels::page>
