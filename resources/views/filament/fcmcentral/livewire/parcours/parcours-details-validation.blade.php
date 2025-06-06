<x-filament::card>

    @if (!empty($parcours))
        <form>

            @foreach ($parcours as $parcoursItem)
        
                {{-- <h4 class="text-md font-bold">{{ $parcoursItem['libelle_court'] }}</h4>
                <p class="text-sm">Taux Global : {{ $parcoursItem['taux_global'] ?? '0' }}%</p>
                <p class="text-sm">Taux Stage : {{ $parcoursItem['taux_stage'] ?? 'N/A' }}</p>
                <p class="text-sm">Taux Activité : {{ $parcoursItem['taux_activite'] ?? 'N/A' }}</p>
                <p class="text-sm">Détails du parcours :</p>
                --}}
                <div class="flex items-center justify-between mt1 mb1 " >
                <h3 class="text-2xl font-bold  mt1 mb1">{{ $parcoursItem['parcours']['libelle_long'] }}</h3>
                <div class="flex items-center space-x-2">

                        {{-- Bouton AValiderProposition --}}
                        @if(!empty($selectedItems['aValiderPropos']))
                        <x-filament::button wire:click="$dispatch('open-modal', { id: 'aValiderModal' })" 
                        id="openAValiderModal" color="info" 
                        data-modal-target="aValiderModal"
                        >Voir les Propositions</x-filament::button>
                        @endif

                        {{-- Bouton Restore --}}
                        <x-filament::icon-button
                        icon="heroicon-m-arrow-uturn-left"
                        tooltip="Restore Data"
                        color="warning"
                        size="lg"
                        wire:click="$dispatch('open-modal', { id: 'restoreModal' })"
                        class="mr1"
                        />

                        {{-- Bouton Reboot --}}
                        <x-filament::icon-button
                        icon="heroicon-m-arrow-path"
                        tooltip="Reboot Data"
                        size="lg"
                        color="danger"
                        wire:click="$dispatch('open-modal', { id: 'rebootModal' })"
                        />




                    </div>
                </div>
                    
                <ul>
                    @foreach ($parcoursItem['parcours']['fonctions'] as $fonction)
                        @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.fonction', [
                            'parcoursId' => $parcoursItem['id'],
                            'fonction' => $fonction,
                            'openSections' => $openSections,
                        ])
                    @endforeach
                </ul>
                
            @endforeach

            <!-- Bouton Submit -->
            <div class="mt-6">
                <x-filament::button wire:click="sauveModal" >
               Sauvegarder
            </x-filament::button>
            </div>
        </form>
    @else
        <p>Aucun parcours Sérialisé trouvé pour ce marin</p>
    @endif

    {{-- <div class="mt-6">
        <pre class="bg-gray-400 p-4 rounded">
            {{ json_encode($selectedItems , JSON_PRETTY_PRINT)}}
           
        </pre>
    </div>  --}}
    
  
 
    <!-- Modal Sauve -->
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.modal')


    @push('styles')
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.styles')
    @endpush

    @push('scripts')
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.scripts')
    @endpush
   


    
</x-filament::card>
