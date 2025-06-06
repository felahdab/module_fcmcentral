{{-- @if(!empty($restore) && $restore->count() > 0)
    <div class="mb-4">
        <x-filament::field-wrapper>
            <x-slot name="label">
                Restaurer depuis un événement précédent
            </x-slot>
            
            <x-filament::select
                wire:model="selectedRestoreEvent"
                placeholder="Choisir un événement à restaurer"
            >
                @foreach($restore as $event)
                    <option value="{{ $event->id }}">
                        {{ ucfirst(str_replace('_', ' ', $event->event_type)) }} - 
                        {{ $event->created_at->format('d/m/Y H:i') }}
                        @if($event->user_name)
                            par {{ $event->user_name }}
                        @endif
                    </option>
                @endforeach
            </x-filament::select>
        </x-filament::field-wrapper>
        
        <div class="mt-2">
            <x-filament::button 
                wire:click="restoreFromEvent"
                :disabled="!$selectedRestoreEvent"
                color="warning"
                size="sm"
            >
                Restaurer
            </x-filament::button>
        </div>
    </div>
@endif --}}



@if(!empty($restore) && $restore->count() > 0)
    <div class="mb-4 p-4">
        <h4 class="font-semibold mb-1 mt1">Historique des événements</h4>
        
        <x-filament::input.wrapper >
            <x-slot name="label" class="mb-1 mt1">
                Restaurer depuis un événement précédent
            </x-slot>
            
            <x-filament::input.select
                wire:model.live="selectedRestoreEvent"
                placeholder="Choisir un événement à restaurer"
            >
                {{-- Option par défaut --}}
                <option value=""  selected>-- Sélectionner un evenement -- </option>

                @foreach($restore as $event)
                    @php
                        //$eventData = json_decode($event->detail, true);
                        $eventData =$event->detail;
                        $eventTypeLabel = match($event->event_type) {
                            'valid_elmt_marin_parcours' => 'Validation',
                            'annul_elmt_marin_parcours' => 'Annulation',
                            'propos_elmt_marin_parcours' => 'Proposition',
                            default => ucfirst(str_replace('_', ' ', $event->event_type))
                        };
                    @endphp
                    
                    <option value="{{ $event->id }}">
                        [{{ $eventTypeLabel }}] 
                        {{ $event->created_at->format('d/m/Y H:i') }}
                        @if($event->user_name)
                            - {{ $event->user_name }}
                        @endif
                        @if(isset($eventData['commentaire']) && $eventData['commentaire'])
                            - "{{ Str::limit($eventData['commentaire'], 30) }}"
                        @endif
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        
        {{-- Aperçu de l'événement sélectionné --}}
        @if($selectedRestoreEvent && $selectedRestoreEvent!=0)
       
            @php
                $selectedEvent = $restore->find($selectedRestoreEvent);
                $eventData = $selectedEvent ? $selectedEvent->detail : null;
                
            @endphp
            
            @if($selectedEvent && $eventData)
                <div class="mt-3 p-3 bg-blue-50 rounded border">
                    <h5 class="font-medium text-blue-800">Aperçu de l'événement :</h5>
                    <p class="text-sm text-blue-600">
                        <strong>Date :</strong> {{ $selectedEvent->created_at->format('d/m/Y H:i:s') }}<br>
                        <strong>Type :</strong> 
                        @php
                         $selectedEventTypeLabel = match($selectedEvent->event_type) {
                            'valid_elmt_marin_parcours' => 'Validation',
                            'annul_elmt_marin_parcours' => 'Annulation',
                            'propos_elmt_marin_parcours' => 'Proposition',
                            default => ucfirst(str_replace('_', ' ', $selectedEvent->event_type))
                        };
                        @endphp
                        {{ $selectedEventTypeLabel }}<br>
                        @if(isset($eventData['commentaire']))
                            <strong>Commentaire :</strong> {{ $eventData['commentaire'] }}<br>
                        @endif
                        @if(isset($eventData['selectedItems']))
                            <strong>Éléments concernés :</strong> 
                            @foreach($eventData['selectedItems'] as $type => $items)
                                {{ $type }} ({{ count($items) }}), 
                            @endforeach
                        @endif
                    </p>
                </div>
            @endif
        @endif
        
        <div class="mt1 flex gap-2">
            <x-filament::button 
                wire:click="restoreFromEvent"
                :disabled="!$selectedRestoreEvent || $selectedRestoreEvent==0"
                color="warning"
                size="sm"
                icon="heroicon-m-arrow-uturn-left"
            >
                Restaurer cet événement
            </x-filament::button>
            
            @if($selectedRestoreEvent)
                <x-filament::button 
                    {{--wire:click="$set('selectedRestoreEvent', null)"--}}
                    wire:click="$set('selectedRestoreEvent', null);$dispatch('close-modal', { id: 'restoreModal' })"
                    color="gray"
                    size="sm"
                    outlined
                >
                    Annuler
                </x-filament::button>
            @endif
        </div>
    </div>
@endif
