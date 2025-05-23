

<div class="flex items-center justify-between mt1 mb1">
  <div class="flex items-center space-x-2">
      {{-- @if ($type === 'activites' || $type === 'savoirFaires') --}}
          <!-- Si c'est une activité ou un savoir-faire, afficher une case à cocher -->
          <x-filament::input.checkbox
              :checked="isset($selectedItems[$parcoursId][$type][$id])"
              wire:click="$wire.toggleAll('{{ $parcoursId }}','{{ $type }}','{{ $id }}', {{ isset($selectedItems[$parcoursId][$type][$id])? 'false':'true'}})"
              class="mr1"
           
          />
      {{-- @else
          <!-- Si ce n'est pas une activité ou un savoir-faire, afficher une icône -->
          @if (isset($selectedItems[$parcoursId][$type][$id]))
              <!-- Si l'élément est sélectionné -->
              <x-filament::icon-button
                  size="lg"
                  color="success"
                  icon="heroicon-c-check-circle"
                  tooltip="{{ $label }} sélectionné"
                  class="mr1"
              />
          @else
              <!-- Si l'élément n'est pas sélectionné -->
              <x-filament::icon-button
                  size="lg"
                  color="secondary"
                  
                  icon="heroicon-c-document"
                  tooltip="{{ $label }} non sélectionné"
                  class="mr1"
              />
          @endif 
      @endif--}}

      <strong>{{ $label }}</strong>
  </div>

  @if ($type !== 'activites')
      <!-- Boutons pour tout cocher / tout décocher -->
      <div class="flex items-center space-x-2">
          <x-filament::icon-button
              size="lg"
              color="info"
              tooltip="Tout cocher"
              icon="heroicon-c-check-circle"
              wire:click="toggleAll('{{ $parcoursId }}', '{{ $type }}', '{{ $id }}', true)"
          />

          <x-filament::icon-button
              size="lg"
              color="secondary"
              tooltip="Tout décocher"
              icon="heroicon-c-document"
              wire:click="toggleAll('{{ $parcoursId }}', '{{ $type }}', '{{ $id }}', false)"
          />

          <x-filament::icon-button
                size="lg"
                color="{{ isset($openSections[$type.'_'.$id])&&$openSections[$type.'_'.$id]?'warning':'info' }}"
                tooltip="{{ isset($openSections[$type.'_'.$id])&&$openSections[$type.'_'.$id]?'Réduire':'Développer' }}"
                icon="{{ isset($openSections[$type.'_'.$id])&&$openSections[$type.'_'.$id]?'heroicon-c-chevron-up':'heroicon-c-chevron-down' }}"
                
                wire:click="toggleSection('{{  $type.'_'.$id }}')"
                />
      </div>

     
      
  @endif
</div>

