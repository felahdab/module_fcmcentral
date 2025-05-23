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
                <h3 class="text-2xl font-bold  mt1 mb1">{{ $parcoursItem['parcours']['libelle_long'] }}</h3>


                
            
                <ul>
                    @foreach ($parcoursItem['parcours']['fonctions'] as $fonction)
                        <li>

                            <!-- Partie Fonction -->

                            @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcours-details-validation-ligne',[
                                'parcoursId'    => $parcoursItem['id'],
                                'type'          => "fonctions",
                                'id'            => $fonction['id'],
                                'label'         => 'Fonction : '.$fonction['libelle_long'],
                                'libelleLong'   => $fonction['libelle_long'],
                                'libelleCourt'  => $fonction['libelle_court'],
                                'etatValid'     => $fonction['etat_valid'],
                                'dateValidation'=> $fonction['date_validation'],
                                'valideur'      => $fonction['valideur'],
                                'commentaire'   => $fonction['commentaire'],
                                'finDeCourse'   => (empty($fonction['competences'])?true:false),
                                'infos'         => [],
                                

                                
                         ])

                        
                         
                            <!-- Fin Partie Fonction -->  

                    
                          
            
                            <!-- Boucle pour les compétences -->
                            @if (!empty($fonction['competences']))
                            <ul class="collapsible  ml2 mb1 " style="display:{{ isset($openSections['fonctions_'.$fonction['id']])?'block':'none' }}">
                                
                                    @foreach ($fonction['competences'] as $competence)
                                    <li>

                                        
                                            <!-- Partie Competence -->

                                            @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcours-details-validation-ligne',[
                                                    'parcoursId'    => $parcoursItem['id'],
                                                    'type'          => "competences",
                                                    'id'            =>  $competence['id'],
                                                    'label'         => 'Competence : '.$competence['libelle_long'],
                                                    'libelleLong'   => $competence['libelle_long'],
                                                    'libelleCourt'   => $competence['libelle_court'],
                                                    'etatValid'     => $competence['etat_valid'],
                                                    'dateValidation'=> $competence['date_validation'],
                                                    'valideur'      => $competence['valideur'],
                                                    'commentaire'   => $competence['commentaire'],
                                                    'finDeCourse'   => (empty($competence['savoirfaires'])?true:false),
                                                    'infos'         => [],
                                                    
                                                      
                                                    
                                            ])

                           
                                        <!-- Fin Partie Competence -->  
            
                                            <!-- Boucle pour les savoir-faire -->
                                            @if (!empty($competence['savoirfaires']))
                                            <ul class="collapsible    highlight" style="display:{{ isset($openSections['competences_'.$competence['id']])?'block':'none' }}"> 
                                                
                                                    @foreach ($competence['savoirfaires'] as $savoirFaire)
                                                    <li>



                                                             <!-- Partie SavoirFaire -->

                                                             @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcours-details-validation-ligne',[
                                                                'parcoursId'    => $parcoursItem['id'],
                                                                'type'          => "savoirfaires",
                                                                'id'            =>  $savoirFaire['id'],
                                                                'label'         => 'Savoir Faire : '.$savoirFaire['libelle_long'],
                                                                'libelleLong'   => $savoirFaire['libelle_long'],
                                                                'libelleCourt'   => $savoirFaire['libelle_court'],
                                                                'etatValid'     => $savoirFaire['etat_valid'],
                                                                'dateValidation'=> $savoirFaire['date_validation'],
                                                                'valideur'      => $savoirFaire['valideur'],
                                                                'commentaire'   => $savoirFaire['commentaire'],
                                                                'finDeCourse'   => (empty($savoirFaire['activites'])?true:false),
                                                                'infos'         => ['coeff'=>$savoirFaire['coeff'],'duree'=>$savoirFaire['duree'],],
                                                                
                                                      
                                                    

                                                                
                                                        ])
                            
                                                                <!-- Fin Partie SavoirFaire -->  


            
                                                            <!-- Boucle pour les activités -->
                                                            @if (!empty($savoirFaire['activites']))
                                                            {{-- <ul class="collapsible  ml2 mb1" style="display:{{ isset($openSections['savoirfaires_'.$savoirFaire['id']])?'block':'none' }}"> --}}
                                                                <ul class="collapsible  ml2 mb1" >
                                                                    @foreach ($savoirFaire['activites'] as $activite)
                                                                        <li>
                                                                           
                                                                            
                                                                             <!-- Partie Activite -->

                                                                             @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcours-details-validation-ligne',[
                                                                'parcoursId'    => $parcoursItem['id'],
                                                                'type'          => "activites",
                                                                'id'            =>  $activite['id'],
                                                                'label'         => 'Activite : '.$activite['libelle_long'],
                                                                'libelleLong'   => $activite['libelle_long'],
                                                                'libelleCourt'   => $activite['libelle_court'],
                                                                'etatValid'     => $activite['etat_valid'],
                                                                'dateValidation'=> $activite['date_validation'],
                                                                'valideur'      => $activite['valideur'],
                                                                'commentaire'   => $activite['commentaire'],
                                                                'finDeCourse'   => true,
                                                                'infos'         => ['coeff'=>$activite['pivot']['coeff'],'duree'=>$activite['pivot']['duree'],],
                                                                
                                                                
                                                        ])
                                                                                    
                                                                            <!-- Fin Partie SavoirFaire -->  


                                                                        </li>
                                                                    @endforeach
                                                                
                                                            </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                
                                            </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                
                            </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

            








                
            @endforeach

            <!-- Bouton Submit -->
            <div class="mt-6">
                <x-filament::button wire:click="openModal" >
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
    </div> --}}
    
    <!-- Modal de commentaire 
    // slide-over  a gauche
    -->
   
<x-filament::modal 
id="comment" 
alignment="center" 
width="3xl"
icon="heroicon-o-information-circle"
>
    
    <x-slot name="heading">
        <h2 class="text-lg font-bold">Ajouter un commentaire</h2>
    </x-slot>
    <x-slot name="description">
    <textarea wire:model="comment" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"></textarea>
</x-slot>
    <x-slot name="footer">
        <x-filament::button color="gray" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" wire:click="$dispatch('close-modal',{id:'comment'})">Annuler</x-filament::button>
        <x-filament::button color="success" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" wire:click="saveForm">Valider</x-filament::button>
    </x-slot>
</x-filament::modal>


 <!-- Modal Décoche -->
 <x-filament::modal
 id="decocheModal"
 alignment="center"
 width="lg"
 icon="heroicon-o-x-circle"
>
 <x-slot name="heading">
     <h2 class="text-lg font-bold">Confirmer la désactivation</h2>
 </x-slot>
 <x-slot name="description">
     <p>Voulez-vous vraiment désactiver cette option ?</p>
     <textarea
         wire:model.defer="comment"
         class="w-full mt-2 border-gray-300 rounded-md"
         placeholder="Ajoutez un commentaire (facultatif)"
     ></textarea>
 </x-slot>
 <x-slot name="footer">
     <x-filament::button wire:click="$dispatch('close-modal', { id: 'decocheModal' })" color="secondary">Annuler</x-filament::button>
     <x-filament::button wire:click="setEtatValidFalse" color="danger">Confirmer</x-filament::button>
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





    <style>
        .collapsible {
            transition: all 0.3s ease-in-out;
            
        }


        .highlight {
    border: 2px solid #f0f8ff; /* Bordure bleue */
    
    transition: all 0.3s ease-in-out; /* Transition fluide */
    padding: 25px;
    border-radius: 10px;
}


        .mt1{margin-top:15px;}
        .mb1{margin-bottom:25px;}
        .mr1{margin-right:10px;}
        .ml2{margin-left:30px;}
        .ml1{margin-left:15px;}

    </style>

<script>
    Livewire.on('close-modal', ({ id }) => {
        const modal = document.getElementById(id);
        if (modal?.close) modal.close(); // pour <dialog>
        else modal?.classList.remove('is-active'); // ou autre si tu utilises un autre système
    });

    Livewire.on('refresh-livret-fcm', () => {
        Livewire.dispatch('refresh'); // équivalent à $refresh
    });
</script>


  

    
</x-filament::card>
