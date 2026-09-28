<x-filament::section class="section-card">
    <x-slot name="heading">
        <div class="flex items-center justify-between">
            <span>Commentaires et observations ({{ $this->notes->count() }})</span>
        </div>
    </x-slot>
    @if($this->canCreateNote())
        <x-slot name="afterHeader">
            <x-filament::button
                tag="a"
                href="{{ $this->noteCreateUrl() }}"
                icon="heroicon-m-plus"
                size="sm"
            >
                Ajouter une note
            </x-filament::button>
        </x-slot>
    @endif
    @if($this->notes->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-sm">Aucun commentaire enregistré</p>
        </div>
    @else
        {{-- Liste compacte : un extrait par note, le contenu complet s'ouvre dans une modale. --}}
        @php $notesVisibles = 5; @endphp
        <div x-data="{ toutAfficher: false }">
            <ul class="note-liste">
                @foreach($this->notes as $note)
                    @php $contenu = \Filament\Forms\Components\RichEditor\RichContentRenderer::make($note->libelle)->toHtml(); @endphp
                    <li
                        class="note-ligne {{ $note->valide_bcf ? 'note-ligne--validee' : '' }}"
                        @if($loop->index >= $notesVisibles) x-show="toutAfficher" x-cloak @endif
                    >
                        <button
                            type="button"
                            class="note-ligne__bouton no-print"
                            x-on:click="$dispatch('open-modal', { id: 'note-{{ $note->id }}' })"
                        >
                            <div class="note-ligne__meta">
                                <x-filament::badge size="sm" :color="$note->valide_bcf ? 'success' : 'info'">
                                    {{ $note->typeNote?->libelle_court ?? 'Commentaire' }}
                                </x-filament::badge>
                                @if($note->date)
                                    <span>{{ \Carbon\Carbon::parse($note->date)->format('d/m/Y') }}</span>
                                @endif
                                @if($note->redacteur)
                                    <span class="note-ligne__redacteur">· {{ $note->redacteur }}</span>
                                @endif
                                <span class="note-ligne__icones">
                                    @if($note->lienurl)
                                        <x-filament::icon icon="heroicon-m-link" class="h-4 w-4" />
                                    @endif
                                    @if($note->valide_bcf)
                                        <x-filament::icon icon="heroicon-m-check-badge" class="h-4 w-4 text-success-600" />
                                    @endif
                                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                                </span>
                            </div>
                            <p class="note-ligne__extrait">{{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags(str_replace('<', ' <', $contenu)))), 220) }}</p>
                        </button>

                        {{-- Version complète, uniquement à l'impression --}}
                        <div class="note-impression">
                            <strong>{{ $note->typeNote?->libelle_court ?? 'Commentaire' }}</strong>
                            @if($note->date) — {{ \Carbon\Carbon::parse($note->date)->format('d/m/Y') }} @endif
                            @if($note->redacteur) — {{ $note->redacteur }} @endif
                            @if($note->valide_bcf) — ✓ Validé BCF @endif
                            <div class="note-content">{!! $contenu !!}</div>
                        </div>

                        <x-filament::modal id="note-{{ $note->id }}" width="3xl" teleport="body">
                            <x-slot name="heading">
                                {{ $note->typeNote?->libelle_court ?? 'Commentaire' }}
                            </x-slot>
                            <x-slot name="description">
                                {{ $note->date ? \Carbon\Carbon::parse($note->date)->format('d/m/Y') : 'Sans date' }}
                                @if($note->redacteur) · {{ $note->redacteur }} @endif
                                @if($note->valide_bcf) · ✓ Validé BCF @endif
                            </x-slot>

                            <div class="text-sm leading-relaxed fi-prose note-content max-w-none">
                                {!! $contenu !!}
                            </div>

                            <x-slot name="footerActions">
                                @if($this->canEditNote())
                                    <x-filament::button tag="a" href="{{ $this->noteEditUrl($note) }}" icon="heroicon-m-pencil-square">
                                        Modifier
                                    </x-filament::button>
                                @endif
                                @if($note->lienurl)
                                    <x-filament::button tag="a" href="{{ $note->lienurl }}" target="_blank" color="gray" icon="heroicon-m-arrow-top-right-on-square">
                                        Lien associé
                                    </x-filament::button>
                                @endif
                                <x-filament::button color="gray" x-on:click="close">
                                    Fermer
                                </x-filament::button>
                            </x-slot>
                        </x-filament::modal>
                    </li>
                @endforeach
            </ul>

            @if($this->notes->count() > $notesVisibles)
                <div class="text-center mt-3 no-print">
                    <x-filament::link tag="button" type="button" size="sm" x-on:click="toutAfficher = !toutAfficher">
                        <span x-show="!toutAfficher">Afficher les {{ $this->notes->count() - $notesVisibles }} commentaires plus anciens</span>
                        <span x-show="toutAfficher" x-cloak>Masquer les commentaires plus anciens</span>
                    </x-filament::link>
                </div>
            @endif
        </div>
    @endif
</x-filament::section>
