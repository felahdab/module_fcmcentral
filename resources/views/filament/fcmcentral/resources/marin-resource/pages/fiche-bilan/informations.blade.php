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
                <span class="text-sm font-medium text-gray-700">Cohorte:</span>
                <span class="text-sm text-gray-900">
                    {{ $this->marin->complementsFcm?->cohorte?->libelle_long ?? 'N/A' }}
                </span>
            </div>

        </div>
    </x-filament::section>

    {{-- ÉQUIPE PÉDAGOGIQUE --}}
    <x-filament::section class="section-card">
        <x-slot name="heading">Équipe pédagogique</x-slot>
        <div class="space-y-3">
            <div class="flex justify-between border-b pb-2">
                <span class="text-sm font-medium text-gray-700">Unité:</span>
                <span class="text-sm text-gray-900">{{ $this->marin->unite?->libelle_long ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-sm font-medium text-gray-700">Mentor:</span>
                <span class="text-sm text-gray-900">
                    @php $mentorsBilan = $this->marin->complementsFcm?->mentors ?? collect(); @endphp
                    @if($mentorsBilan->isNotEmpty())
                        {{ $mentorsBilan->map(fn($m) => trim(($m->grade?->libelle_long ?? '').' '.$m->nom))->join(', ') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
        </div>
    </x-filament::section>

    {{-- HISTORIQUE DES ÉTATS --}}
    <x-filament::section class="section-card">
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <span>Jalons et gel FCM</span>
            </div>
        </x-slot>

        <div class="space-y-3">
            {{-- DÉBUT SÉDIMENTATION --}}
            <div class="flex items-center justify-between p-2 ">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-5 h-5 " />
                    <span class="text-sm font-medium text-gray-900">Date d'entrée en FCM</span>
                </div>
                <span class="text-sm text-gray-700">
                    {{ $this->getDateSedimentationFormatee() }}
                </span>
            </div>

            {{-- VERT FCM --}}
            <div class="flex items-center justify-between p-2 ">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 " />
                    <span class="text-sm font-medium text-gray-900">Date de fin de sédimentation</span>
                </div>
                <span class="text-sm text-gray-700">
                    {{ $this->getDateFcmFormatee() }}
                </span>
            </div>

            {{-- VERT COMPÉTENCE --}}
            <div class="flex items-center justify-between p-2 ">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5 " />
                    <span class="text-sm font-medium text-gray-900">Date de vert compétence</span>
                </div>
                <span class="text-sm text-gray-700">
                    {{ $this->getDateCompetenceFormatee() }}
                </span>
            </div>
        </div>

        @if($this->historiqueEtats->isEmpty())
            <div class="text-center py-4 text-gray-500 text-sm">
                Aucun gel enregistré
            </div>
        @else
            <div class="text-left py-4 text-gray-500 text-sm">
               Les Gels
            </div>
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
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

</div>