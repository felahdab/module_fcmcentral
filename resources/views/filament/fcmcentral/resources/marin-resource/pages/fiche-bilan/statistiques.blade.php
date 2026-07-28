{{-- STATISTIQUES (DYNAMIQUE : 2 à 4 COLONNES) --}}
<div class="grid {{ $this->hasMultipleNonZeroStats() ? 'grid-sections-4' : 'grid-sections-2' }} gap-4 mb-6">

    {{-- PROGRESSION GLOBALE --}}
    <x-filament::section class="text-center section-card">
        <x-slot name="heading">Progression globale</x-slot>
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
            <x-slot name="heading">Savoir-faire non commencés</x-slot>
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
        <x-slot name="heading">SF - Enseignement à distance</x-slot>
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
        <x-slot name="heading">C - Compagnonnage</x-slot>
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
        <x-slot name="heading">C - Stage</x-slot>
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
        <x-slot name="heading">C - Enseignement à distance</x-slot>
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
