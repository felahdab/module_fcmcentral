<div class="mb-6">
    <x-filament::section>
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold">
                        {{ $this->marin->grade?->libelle_court }} {{ $this->marin->prenom }} {{ $this->marin->nom }}
                    </h1>
                    @include('fcmcentral::filament.fcmcentral.resources.marin-resource.partials.statut-fcm', ['marin' => $this->marin])
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm opacity-90">
                    <div><strong>Matricule:</strong> {{ $this->marin->matricule }}</div>
                    <div><strong>Spécialité:</strong> {{ $this->marin->specialite?->libelle_long ?? 'N/A' }}</div>
                    <div><strong>Unité:</strong> {{ $this->marin->unite?->libelle_long ?? 'N/A' }}</div>
                    <div><strong>Cohorte:</strong> {{ $this->marin->complementsFcm?->cohorte?->libelle_long ?? 'N/A' }}</div>
                </div>
                @php $mentors = $this->marin->complementsFcm?->mentors ?? collect(); @endphp
                @if($mentors->isNotEmpty())
                    <div class="mt-2 text-sm opacity-90">
                        <strong>Mentor(s):</strong>
                        {{ $mentors->map(fn($m) => trim(($m->grade?->libelle_long ?? '').' '.$m->prenom.' '.$m->nom))->join(', ') }}
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
