<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-filament::icon 
                    icon="heroicon-o-document-text" 
                    class="w-8 h-8 text-primary-600"
                />
                <div>
                    <h3 class="text-lg font-semibold">Livret du Mentor DEASM</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Guide complet pour accompagner vos mentorés
                    </p>
                </div>
            </div>
            
            <x-filament::button
                tag="a"
                href="{{ $this->getPdfUrl() }}"
                target="_blank"
                icon="heroicon-m-arrow-down-tray"
            >
                Télécharger
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
