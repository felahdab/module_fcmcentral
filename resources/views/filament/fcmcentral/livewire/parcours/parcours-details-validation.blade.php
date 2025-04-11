

<x-filament::card>
    <h2 class="text-xl font-bold mb-4">Parcours Sérialisé</h2>
    @if (!empty($parcours))
    <h2 class="text-xl font-bold mb-4">Parcours à valider</h2>

    @foreach ($parcours as $parcoursItem)
    <h4 class="text-md font-bold">{{ $parcoursItem['libelle_court'] }}</h4>
    <p class="text-sm ">Taux Global : {{ $parcoursItem['taux_global'] ?? 'N/A' }}</p>
    <p class="text-sm ">Taux Stage : {{ $parcoursItem['taux_stage'] ?? 'N/A' }}</p>
    <p class="text-sm ">Taux Activité : {{ $parcoursItem['taux_activite'] ?? 'N/A' }}</p>
    <p class="text-sm ">Détails du parcours :</p>

  

    <h3 class="text-2xl font-bold yellow mt1 mb1">Parcours : {{ $parcoursItem['parcours']['libelle_long'] }}</h3>
                       



    <ul>
        <li>
      @if ($parcoursItem['parcours']['fonctions'] )
          <ul >
              @foreach ($parcoursItem['parcours']['fonctions']  as $indexFonction => $fonction)
                  <li>
                      <div class="flex items-center justify-between  mt1 mb1">
                          
                          <strong class="greenyellow">Fonction : {{ $fonction['libelle_long'] }}</strong>
                         <div class="flex items-center space-x-2">
                        
                           
                            <input type="checkbox" wire:click="handleCheckboxUpdate({{ $parcoursItem['id'] }}, {{ $indexFonction }})" />
                          <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                        
                         </div>
                      </div>
                      @if ($fonction['competences'])
                          <ul class="collapsible">
                              @foreach ($fonction['competences'] as $indexCompetence => $competence)
                                  <li>
                                      <div class="flex items-center justify-between  mt1 mb1">
                                          
                                          <strong class="aquamarine">Compétence : {{ $competence['libelle_long'] }}</strong>
                                          <div class="flex items-center space-x-2">
                                            
                                            <input type="checkbox" wire:click="handleCheckboxUpdate({{ $parcoursItem['id'] }}, {{ $indexFonction }}, {{ $indexCompetence }})" />
                                          <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                        </div>
                                      </div>
                                      @if ($competence['savoirfaires'])
                                          <ul class="collapsible">
                                              @foreach ($competence['savoirfaires'] as $indexSavoirfaire => $savoirFaire)
                                                  <li>
                                                    <div class="flex items-center justify-between  mt1 mb1">
                                                   
                                                      <strong class="blue">Savoir-faire : {{ $savoirFaire['libelle_long'] }}</strong>
                                                      <div class="flex items-center space-x-2">
                                                        <input type="checkbox" wire:click="handleCheckboxUpdate({{ $parcoursItem['id'] }}, {{ $indexFonction }}, {{ $indexCompetence }}, {{ $indexSavoirfaire }} )" />
                                                    <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                </div>
                                                  </div>
                                                    @if ($savoirFaire['activites'])
                                                    <ul class="collapsible">
                                                        @foreach ($savoirFaire['activites'] as $indexActivite => $activite)
                                                            <li>
                                                                <div class="flex items-center justify-between  mt1 mb1">
                                                                 
                                                                Activite : {{ $activite['libelle_long'] }}
                                                            <div class="flex items-center space-x-2">
                                                                <input type="checkbox" wire:click="handleCheckboxUpdate({{ $parcoursItem['id'] }}, {{ $indexFonction }}, {{ $indexCompetence }}, {{ $indexSavoirfaire }}, {{ $indexActivite }} )" />
                                                               
                                                            </div>
                                                        </div>
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
      @endif
      </li>
      </ul>


    @endforeach
    @else
    <p>Aucun parcours Sérialisé trouvé pour ce marin</p>
    @endif

    <style>
        .tree-view ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
    
        .tree-view ul ul {
            margin-left: 20px;
            border-left: 1px solid #ccc;
            padding-left: 10px;
        }
    
        .tree-view li {
            margin: 5px 0;
        }
    
        .tree-view strong {
           /* color:darkgrey; */
        }

        .yellow{
          color:yellow;
        }

        .greenyellow{
          color:greenyellow
        }

        .aquamarine {
          color:aquamarine;
        }

        .blue {
          color:#007bff;
        }
    
        .tree-view .collapsible {
            display: none; /* Par défaut, les sous-éléments sont cachés */
        }
    
        .tree-view .toggle-btn {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 14px;
        }
    
        .tree-view .toggle-btn:focus {
            outline: none;
        }

        .mt1{margin-top:15px;}
        .mb1{margin-bottom:15px;}
        .mr1{margin-right:15px;}
        .ml1{margin-left:15px;}



        .collapsible {
    display: none;
}

.toggle-btn {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 14px;
}

.toggle-btn:focus {
    outline: none;
}


    </style>
    
    <script>
        function toggleCollapse(button) {
    const collapsible = button.parentElement.parentElement.nextElementSibling;

    if (collapsible && collapsible.classList.contains('collapsible')) {
        if (collapsible.style.display === 'none' || collapsible.style.display === '') {
            collapsible.style.display = 'block';
            button.textContent = '[-]';
        } else {
            collapsible.style.display = 'none';
            button.textContent = '[+]';
        }
    }
}

document.addEventListener('livewire:load', () => {
    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', function () {
            toggleCollapse(this);
        });
    });
});


window.addEventListener('notify', event => {
        // Afficher une alerte avec le message reçu
        alert(event.detail.message);

        // Vous pouvez également utiliser une bibliothèque comme Toastify ou SweetAlert pour des notifications plus élégantes
        // Exemple avec SweetAlert :
        // Swal.fire({
        //     title: 'Notification',
        //     text: event.detail.message,
        //     icon: 'info',
        //     confirmButtonText: 'OK'
        // });
    });



    </script>
</x-filament::card>




