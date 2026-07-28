@if($this->activitesEnAttente->count() > 0)
    <x-filament::section class="section-card mb-6">
        <x-slot name="heading">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                Objectifs en attente de validation ({{ $this->activitesEnAttente->count() }})
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
