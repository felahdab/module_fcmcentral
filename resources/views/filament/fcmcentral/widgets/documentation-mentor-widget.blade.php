<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $pdfUrl   = $this->getPdfUrl();
            $pdfLabel = $this->getPdfLabel();
        @endphp

        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="w-8 h-8 text-primary-600 shrink-0"
                />
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $pdfLabel }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Guide complet pour accompagner vos mentorés
                    </p>
                </div>
            </div>

            @if ($pdfUrl)
                <div class="flex items-center gap-2 shrink-0">
                    <x-filament::button
                        tag="a"
                        href="{{ $pdfUrl }}"
                        target="_blank"
                        icon="heroicon-m-eye"
                        color="gray"
                        outlined
                    >
                        Consulter
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        href="{{ $pdfUrl }}"
                        download
                        icon="heroicon-m-arrow-down-tray"
                    >
                        Télécharger
                    </x-filament::button>
                </div>
            @else
                <span class="text-sm text-gray-400 dark:text-gray-500 italic">
                    Aucun document disponible pour votre parcours
                </span>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
