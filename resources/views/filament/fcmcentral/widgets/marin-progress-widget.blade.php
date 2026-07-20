<x-filament-widgets::widget>
    <x-filament::section>

        {{-- ✅ Calculer en premier --}}
        @php
            $progressData = $this->getProgressData();
            $progress     = $progressData['progress'];
        @endphp

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Progression du parcours
                </h3>
                {{-- <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $progressData['completed'] }}h / {{ $progressData['total'] }}h
                </span> --}}
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Heures validées</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $progress }}%</span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div class="bg-primary h-4 rounded-full transition-all duration-500 ease-out"
                         style="width: {{ max($progress, 0) }}%; background:#00AA00;">
                    </div>
                </div>

                {{-- <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $progressData['completed'] }}h validées</span>
                    <span>{{ $progressData['total'] - $progressData['completed'] }}h restantes</span>
                </div> --}}
            </div>
            {{-- @if(config('app.debug'))
            <p class="text-xs text-red-400">DEBUG → progress={{ $progress }} | total={{ $progressData['total'] }} | completed={{ $progressData['completed'] }}</p>
        @endif --}}

            <div class="flex justify-center gap-3">
                <x-filament::button
                    tag="a"
                    href="{{ \Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm::getUrl() }}"
                    color="primary">
                    Voir mon livret FCM
                </x-filament::button>

                @if ($pdfUrl = $this->getPdfUrl())
                    <x-filament::button
                        tag="a"
                        href="{{ $pdfUrl }}"
                        target="_blank"
                        color="success"
                        icon="heroicon-o-document-arrow-down">
                        Livret PDF du parcours
                    </x-filament::button>
                @endif
            </div>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
