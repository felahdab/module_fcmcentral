<x-filament-panels::page>

    {{-- ═══ TABS ═══ --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex gap-1" aria-label="Tabs">

            <button wire:click="setTab('stages')" type="button"
                @class([
                    'flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $this->activeTab === 'stages',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' => $this->activeTab !== 'stages',
                ])>
                <x-heroicon-o-academic-cap class="w-4 h-4" />
                Stages
            </button>

            <button wire:click="setTab('marins')" type="button"
                @class([
                    'flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $this->activeTab === 'marins',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' => $this->activeTab !== 'marins',
                ])>
                <x-heroicon-o-user-group class="w-4 h-4" />
                Marins
            </button>

        </nav>
    </div>

    {{-- ═══ CONTENU PAR ONGLET ═══ --}}

    {{-- ─── Onglet STAGES ─────────────────────────────────────────────────── --}}
    @if($this->activeTab === 'stages')

        {{-- Filtre : Parcours uniquement --}}
        <x-filament::section class="mb-5">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-funnel class="w-4 h-4 text-gray-400" />
                    Filtre
                </div>
            </x-slot>
            <div class="max-w-sm">
                {{ $this->formParcours }}
            </div>
        </x-filament::section>

        {{-- Tableau des stages --}}
        <x-filament::section>
            <x-slot name="heading">
                @if(filled($this->selectedParcoursId))
                    @php $stats = $this->getStatsParStage(); @endphp
                    Stages
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ count($stats) }}
                    </span>
                @else
                    Stages
                @endif
            </x-slot>

            @if(blank($this->selectedParcoursId))
                <div class="flex flex-col items-center justify-center py-16 text-center text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-funnel class="w-12 h-12 mb-4 opacity-30" />
                    <p class="text-sm font-medium">Sélectionnez un parcours pour afficher ses stages.</p>
                </div>

            @elseif(count($stats) === 0)
                <div class="flex flex-col items-center justify-center py-16 text-center text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-academic-cap class="w-12 h-12 mb-4 opacity-30" />
                    <p class="text-sm font-medium">Aucun stage trouvé pour ce parcours.</p>
                </div>

            @else
                <div class="overflow-x-auto -mx-6 -mb-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stage</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">Effectué</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">Proposé</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-red-500 dark:text-red-400">Non effectué</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Progression</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($stats as $stat)
                                @php
                                    $pct = $stat['total'] > 0 ? round(($stat['effectues'] / $stat['total']) * 100) : 0;
                                    $barColor = $pct >= 80 ? '#22c55e' : ($pct >= 40 ? '#eab308' : '#ef4444');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-6 py-3.5">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $stat['libelle'] ?: '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $stat['total'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            {{ $stat['effectues'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                            {{ $stat['proposes'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                            {{ $stat['non_effectues'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3 min-w-36">
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full transition-all duration-500"
                                                    style="width:{{ $pct }}%; background-color:{{ $barColor }}">
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold tabular-nums w-9 text-right"
                                                style="color:{{ $barColor }}">
                                                {{ $pct }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <button
                                            wire:click="selectStageAndShowMarins('{{ $stat['uuid'] }}')"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                                        >
                                            Voir les marins
                                            <x-heroicon-m-arrow-right class="w-3 h-3" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

    @endif

    {{-- ─── Onglet MARINS ─────────────────────────────────────────────────── --}}
    @if($this->activeTab === 'marins')

        {{-- Filtres : Parcours + Stage --}}
        <x-filament::section class="mb-5">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-funnel class="w-4 h-4 text-gray-400" />
                    Filtres
                </div>
            </x-slot>

{{ $this->formMarins }}
        </x-filament::section>

        {{-- Bandeau filtres actifs --}}
        @if(filled($this->selectedStageUuid) || filled($this->selectedStatut))
            @php
                $stageLabel = collect($this->buildStageOptions())->get($this->selectedStageUuid);
                $statutLabel = match($this->selectedStatut) {
                    'effectue'     => 'Effectué',
                    'propose'      => 'Proposé',
                    'non_effectue' => 'Non effectué',
                    default        => null,
                };
                $statutColor = match($this->selectedStatut) {
                    'effectue'     => 'green',
                    'propose'      => 'yellow',
                    'non_effectue' => 'red',
                    default        => 'primary',
                };
            @endphp
            <div class="mb-4 flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-2.5">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mr-1">Filtres actifs :</span>

                @if(filled($this->selectedStageUuid))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <x-heroicon-m-academic-cap class="w-3 h-3" />
                        {{ $stageLabel ?? $this->selectedStageUuid }}
                    </span>
                @endif

                @if($statutLabel)
                    <span @class([
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                        'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'  => $statutColor === 'green',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' => $statutColor === 'yellow',
                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $statutColor === 'red',
                    ])>
                        {{ $statutLabel }}
                    </span>
                @endif

                <button wire:click="clearStageFilter" type="button"
                    class="ml-auto inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-medium transition-colors">
                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                    Effacer les filtres
                </button>
            </div>
        @endif

        {{-- Table Filament --}}
        @if(blank($this->selectedParcoursId))
            <x-filament::section>
                <div class="flex flex-col items-center justify-center py-16 text-center text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-funnel class="w-12 h-12 mb-4 opacity-30" />
                    <p class="text-sm font-medium">Sélectionnez un parcours pour afficher les marins.</p>
                </div>
            </x-filament::section>
        @else
            {{ $this->table }}
        @endif

    @endif

<x-filament-actions::modals />
</x-filament-panels::page>


