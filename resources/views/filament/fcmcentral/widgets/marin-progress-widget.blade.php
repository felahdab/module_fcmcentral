{{-- filepath: resources/views/fcmcentral/filament/fcmcentral/widgets/marin-progress-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Progression du parcours
                </h3>
                {{-- <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $this->getProgressData()['completed'] }}/{{ $this->getProgressData()['total'] }} heures
                </span> --}}
            </div>
            
            @php
                $progressData = $this->getProgressData();
                $progress = $progressData['progress'];
            @endphp
            
            <div class="space-y-2">
               <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Heures validées</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $progress }}%</span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div class="bg-primary-600 h-4 rounded-full transition-all duration-500 ease-out" 
                         style="width: {{ $progress }}%">
                    </div>
                </div>
                
                {{-- <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $progressData['completed'] }} heures validées</span>
                    <span>{{ $progressData['total'] - $progressData['completed'] }} heures restantes</span>
                </div> --}}
            </div>
            {{-- @if($details)
            <div class="mt-4 space-y-2 text-xs text-gray-500 dark:text-gray-400 border-t pt-3">
                <div class="flex justify-between">
                    <span>Savoir-faires validés :</span>
                    <span class="font-medium">{{ $details['savoir_faires_valides'] ?? 0 }}/{{ $details['total_savoir_faires'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Activités validées :</span>
                    <span class="font-medium">{{ $details['activites_validees'] ?? 0 }}/{{ $details['total_activites'] ?? 0 }}</span>
                </div>
            </div>
            @endif --}}
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

