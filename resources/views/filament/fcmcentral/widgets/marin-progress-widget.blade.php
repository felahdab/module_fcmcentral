<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium">Progression de votre parcours FCM</h3>
                <span class="text-2xl font-bold text-primary-600">{{ $this->getProgressData()['progress'] }}%</span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-primary-600 h-4 rounded-full transition-all duration-300" 
                     style="width: {{ $this->getProgressData()['progress'] }}%"></div>
            </div>
            
            <div class="flex justify-between text-sm text-gray-600">
                <span>{{ $this->getProgressData()['completed'] }} activités validées</span>
                <span>{{ $this->getProgressData()['total'] }} activités au total</span>
            </div>
            
            <div class="flex justify-center">
                <x-filament::button 
                    tag="a" 
                    href="{{ \Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm::getUrl() }}"
                    color="primary">
                    Voir mon livret FCM
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
