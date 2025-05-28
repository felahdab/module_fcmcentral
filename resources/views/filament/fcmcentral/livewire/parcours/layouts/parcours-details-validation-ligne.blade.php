@php
   
   $indentation = match($type){
    'fonctions' => 'ml-0',
    'competences' => 'ml-4',
    'savoirfaires' => 'ml-8',
    'activites' => 'ml-12',
    'default' =>'',
   };

   $isOpen = isset($openSections[$type.'_'.$id]) && $openSections[$type.'_'.$id];

  
   $isChecked = $etatValid?? false;

   
   $duree = (($type === 'activites')||($type === 'savoirfaires') ? $infos['coeff'] :'');
   


@endphp
<div class="flex items-center justify-between mt1 mb1 {{$indentation}}" >
    <div class="flex items-center space-x-2">
       
       
           
                {{-- 
                     //trop instable que des soucis lors des cliques
                    <x-filament::input.checkbox
                        wire:click="toggleCheck('{{$type}}',{{$id}});"
                        :checked="isset($this->selectedItems[$type][$id])"
                        data-id="{{$type}}-{{$id}}"
                        onchange="checkboxEtat(event);"
                        class="mr1"
                />  --}}

                

                
            

        
                        
            {{-- Si pas checke --}}
            @if (!$isChecked)

                {{-- Boucle Etat bouton --}}
                @if (isset($selectedItems['gestion'][$type][$id]) && $selectedItems['gestion'][$type][$id])

                    <x-filament::icon-button
                        size="lg"
                        color="success"
                        {{-- icon="heroicon-c-x-circle" --}}
                        icon="heroicon-o-check-circle"
                        tooltip="Tout décocher"
                        wire:click="toggleCheck('{{$type}}', {{$id}});"
                        class="tree-checkbox"
                        {{-- x-on:click="setTimeout(() => enforceCheckedState(@entangle('selectedItems').defer), 300)" --}}
                    />

                @else

                    <x-filament::icon-button
                        size="lg"
                        color="gray"
                        icon="heroicon-o-check-circle"
                        tooltip="Tout cocher"
                        wire:click="toggleCheck('{{$type}}', {{$id}});"
                       
                      
                    />

                @endif
                {{-- Fin boucle etat --}}
                
            @else
            
            <x-filament::icon-button
            size="lg"
            color="success"
            icon="heroicon-c-shield-check"
            tooltip="Validation le {{$dateValidation }} par {{$valideur}}"
            class="tree-checkbox"
           
        />
        @if(($type === 'activites')||($type === 'savoirfaires'))
        <x-filament::icon-button
                size="lg"
                color="warning"
                icon="heroicon-s-x-circle"
                tooltip="Décochez"
                wire:click="decocheModal('{{$type}}', {{$id}})"
            />
            @endif
            @endif
            {{-- Fin Si pas checke --}}
            <strong  class="ml1">{{ $label }} </strong>
     

            
              
              
               
                
        </div>
       
        <div class="flex items-center space-x-2">
            
           

            
            @if(($type === 'activites')||($type === 'savoirfaires'))
            <x-filament::icon-button
                size="lg"
                color="{{ $isChecked ?'success':'gray' }}"
                {{-- icon="heroicon-c-check-badge" --}}
                icon="heroicon-c-information-circle"
                tooltip="Voir les infos "
                wire:click="infosModal('{{$type}}', {{$id}}, '{{ $dateValidation ?? '' }}', '{{ $valideur ?? 'Inconnu' }}', '{{ $commentaire ?? '' }}','{{ $libelleCourt }}','{{ $libelleLong }}','{{$duree}}')"
            />
            @endif

            @if(($type === 'fonctions')||($type === 'competences'))
            <x-filament::icon-button
                size="lg"
                color="{{ $isOpen ?'warning':'info' }}"
                tooltip="{{ $isOpen ?'Réduire':'Développer' }}"
                icon="{{ $isOpen ?'heroicon-c-chevron-up':'heroicon-c-chevron-down' }}"
                
                wire:click="toggleSection('{{ $type.'_'.$id }}')"
                />
                @endif


            @if (!$finDeCourse)
           
                @endif
        </div>
       
    </div>


    
    
    