<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-primary-800 mb-2">
                Bienvenue {{ $this->getMarin()?->prenom }} !
            </h2>
            <p class="text-primary-700">
                Suivez votre progression dans votre formation continue des marins.
            </p>
        </div>
        
        {{ $this->getWidgets() }}
    </div>
</x-filament-panels::page>
