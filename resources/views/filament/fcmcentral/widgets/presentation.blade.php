<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Présentation
            </h3>

            @if($this->peutModifier())
                <x-filament::button tag="a" href="{{ $this->urlEdition() }}" size="sm" icon="heroicon-o-pencil-square">
                    Modifier
                </x-filament::button>
            @endif
        </div>

        @if(filled($this->data['texte'] ?? null))
            <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300">
                {!! nl2br(e($this->data['texte'])) !!}
            </div>
        @else
            <p class="text-sm text-gray-400 dark:text-gray-500">Aucune présentation configurée pour le moment.</p>
        @endif

        @php
            $logos = collect($this->data['logos'] ?? [])->filter(fn ($logo) => !empty($logo['image']))->values();
        @endphp

        @if($logos->isNotEmpty())
            <div class="mt-4 grid gap-3 justify-start" style="grid-template-columns: repeat(4, 5rem);">
                @foreach($logos as $logo)
                    @php
                        $src = \Illuminate\Support\Facades\Storage::disk('s3-public')->url($logo['image']);
                    @endphp
                    <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800">
                        @if(!empty($logo['url']))
                            <a href="{{ $logo['url'] }}" target="_blank" rel="noopener" class="flex h-full w-full items-center justify-center" title="{{ $logo['libelle'] ?? '' }}">
                                <img src="{{ $src }}" alt="{{ $logo['libelle'] ?? '' }}" class="h-full max-h-14 w-full object-contain" />
                            </a>
                        @else
                            <img src="{{ $src }}" alt="{{ $logo['libelle'] ?? '' }}" title="{{ $logo['libelle'] ?? '' }}" class="h-full max-h-14 w-full object-contain" />
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
