<x-filament::modal 
id="sauve" 
alignment="center" 
width="3xl"
icon="heroicon-o-information-circle"
>
    
    <x-slot name="heading">
        <h2 class="text-lg font-bold">Ajouter un commentaire</h2>
    </x-slot>
    <x-slot name="description">
    <textarea wire:model="comment" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 "></textarea>
</x-slot>
    <x-slot name="footer">
        <x-filament::button color="gray" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" wire:click="$dispatch('close-modal',{id:'sauve'})">Annuler</x-filament::button>
        <x-filament::button color="success" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" wire:click="save('valid')">Valider</x-filament::button>
    </x-slot>
</x-filament::modal>


 <!-- Modal Annul -->
 <x-filament::modal
 id="annul"
 alignment="center"
 width="3xl"
 icon="heroicon-o-x-circle"
>
 <x-slot name="heading">
     <h2 class="text-lg font-bold">Confirmer la désactivation</h2>
 </x-slot>
 <x-slot name="description">
     <p class="p-8">Voulez-vous vraiment désactiver cette option ?</p>
     <textarea
         wire:model="comment"
         class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 text-primary-500  "
         placeholder="Ajoutez un commentaire (facultatif)"
     ></textarea>
 </x-slot>
 <x-slot name="footer">
     <x-filament::button wire:click="$dispatch('close-modal', { id: 'annul' })" color="secondary">Annuler</x-filament::button>
     <x-filament::button wire:click="save('annul')" color="danger">Confirmer</x-filament::button>
 </x-slot>
</x-filament::modal>



<!-- Modal Propos -->
<x-filament::modal
id="propos"
alignment="center"
width="3xl"
icon="heroicon-o-x-circle"
>
<x-slot name="heading">
    <h2 class="text-lg font-bold">Confirmer la Proposition</h2>
</x-slot>
<x-slot name="description">
    <p class="p-8">Vous souhaitez proposer a votre mentor ces validations</p>
    <textarea
        wire:model="comment"
        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 text-primary-500  "
        placeholder="Ajoutez un commentaire (facultatif)"
    ></textarea>
</x-slot>
<x-slot name="footer">
    <x-filament::button wire:click="$dispatch('close-modal', { id: 'propos' })" color="secondary">Annuler</x-filament::button>
    <x-filament::button wire:click="save('propos')" color="success">Confirmer</x-filament::button>
</x-slot>
</x-filament::modal>


<!-- Modal Infos -->
<x-filament::modal
id="infosModal"
alignment="center"
width="lg"
icon="heroicon-o-information-circle"
>
<x-slot name="heading">
    <h2 class="text-lg font-bold">Informations</h2>
</x-slot>
<x-slot name="description">
    <p><strong>Type :</strong> {{ $popupData['type'] ?? 'Non disponible' }}</p>
    <p><strong>ID :</strong> {{ $popupData['id'] ?? 'Non disponible' }}</p>
    <p><strong>Titre :</strong> {{ $popupData['libelleCourt'] ?? 'Non disponible' }}</p>
    <p><strong>Description :</strong> {{ $popupData['libelleLong'] ?? 'Non disponible' }}</p>
    <p><strong>Durée :</strong> {{ $popupData['duree'] ?? 'Non disponible' }}</p>
    <p><strong>Date de validation :</strong> {{ $popupData['dateValidation'] ?? 'Non disponible' }}</p>
    <p><strong>Validé par :</strong> {{ $popupData['valideur'] ?? 'Pas encore valide' }}</p>
    <p><strong>Commentaire :</strong> {{ $popupData['commentaire'] ?? 'Aucun commentaire' }}</p>
</x-slot>
<x-slot name="footer">
    <x-filament::button wire:click="$dispatch('close-modal', { id: 'infosModal' })" color="secondary">Fermer</x-filament::button>
</x-slot>
</x-filament::modal>


<!-- Modal Restore -->
<x-filament::modal
id="restoreModal"
alignment="center"
width="lg"
icon="heroicon-m-arrow-uturn-left"
>
<x-slot name="heading">
    <h2 class="text-lg font-bold">Restore Data</h2>
</x-slot>
<x-slot name="description">
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.selectRestore', ['restore'=>$restore])
</x-slot>

</x-filament::modal>

<!-- Modal Reboot -->
<x-filament::modal
    id="rebootModal"
    alignment="center"
    width="md"
    icon="heroicon-m-arrow-path"
>
    <x-slot name="heading">
        <h2 class="text-lg font-bold text-orange-600">Reboot Data</h2>
    </x-slot>
    
    <x-slot name="description">
        <div class="space-y-4">
            <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <div class="flex items-start space-x-3">
                    <x-heroicon-m-exclamation-triangle class="w-6 h-6 text-orange-500 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-orange-800">Attention</h4>
                        <p class="text-sm text-orange-700 mt-1">
                            Cette action va remettre à zéro tous les éléments de validation du parcours. 
                            Toutes les validations, commentaires et dates seront perdus.
                        </p>
                    </div>
                </div>
            </div>
            
            <div>
                <x-filament::input.wrapper>
                    <x-slot name="label">
                        Commentaire (optionnel)
                    </x-slot>
                    <x-filament::input
                        type="text"
                        wire:model="rebootComment"
                        placeholder="Raison du reboot..."
                    />
                </x-filament::input.wrapper>
            </div>
        </div>
    </x-slot>
    
    <x-slot name="footerActions">
        <div class="flex justify-end space-x-2">
            <x-filament::button
                color="gray"
                outlined
                x-on:click="$dispatch('close-modal', { id: 'rebootModal' })"
            >
                Annuler
            </x-filament::button>
            
            <x-filament::button
                color="warning"
                icon="heroicon-m-arrow-path"
                wire:click="save('reboot')"
               
            >
                Confirmer le Reboot
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>




{{-- Oblige de faire un bouton invisible pour afficher un Modal Automatique (script dans Scripts.blade.php) --}}

@if(!empty($selectedItems['aValiderPropos']))
<x-filament::modal 
   slide-over
    id="aValiderModal"
    width="2xl"
    
    alignment="center"
    {{-- style="z-index:50px;"
    icon="heroicon-o-information-circle" --}}
>
    <x-slot name="heading"  >
        <h2 class="text-xl font-bold" style="padding-top:60px;padding-bottom:60px;" >
            @if (auth()->user()->hasRole('mentor')|| $fcmMarin->mentor!=null)
            Suggestions du marin (MENTOR)
            @else
            Vos Suggestions en attente
            @endif
        
        </h2>
    </x-slot>

    <x-slot name="description" > 
      
        @foreach ($selectedItems['aValiderPropos'] as $key => $aValiderProposItem)
            <h5 class="mb-1 font-semibold text-left">{{ ucfirst($key) }}</h5>
            <ul class="ml-1 mb-2 list-disc text-sm">
                @foreach ($aValiderProposItem as $aValiderProposItemDetail)
                    <li class="mb-1 ml-2">
                        <div class="flex items-center justify-between">
                            <span>
                                {{ $aValiderProposItemDetail['libelle_court'] }} –
                                suggéré le {{ $aValiderProposItemDetail['date_proposition'] }}
                            </span>
                            <x-filament::icon-button
                                size="sm"
                                color="primary"
                                icon="heroicon-c-information-circle"
                                tooltip="{{ $aValiderProposItemDetail['libelle_long'] }}"
                            />
                        </div>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </x-slot>

    <x-slot name="footer">
        <x-filament::button color="danger" wire:click="save('annulPropos')">
            Annuler tout
        </x-filament::button>

        <x-filament::button color="success" wire:click="save('validPropos')">
            Valider tout
        </x-filament::button>
    </x-slot>
</x-filament::modal>
@endif



 