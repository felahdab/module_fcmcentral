<x-filament::page>
    <div class="space-y-4">

   
    
      <a href="#" onclick="Livewire.emit('openModal', 'fcmcentral::manage-entity-full-parcours-modal')">test</a>

        <div class="tree-view">
          @if ($parcours)
              
                      <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold yellow mt1 mb1">Parcours : {{ $parcours->libelle_long }}</h1>
                         
                      </div>
                      <ul>
                        <li>
                      @if ($parcours->fonctions->isNotEmpty())
                          <ul >
                              @foreach ($parcours->fonctions as $fonction)
                                  <li>
                                      <div class="flex items-center justify-between  mt1 mb1">
                                          <strong class="greenyellow">Fonction : {{ $fonction->libelle_long }}</strong>
                                         <div class="flex items-center space-x-2">
                                            {{($this->editAction)(['recordId'=>$fonction->id,'type'=>'fonction'])}}
                                          
                                          <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                        
                                         </div>
                                      </div>
                                      @if ($fonction->competences->isNotEmpty())
                                          <ul class="collapsible">
                                              @foreach ($fonction->competences as $competence)
                                                  <li>
                                                      <div class="flex items-center justify-between  mt1 mb1">
                                                          <strong class="aquamarine">Compétence : {{ $competence->libelle_long }}</strong>
                                                          <div class="flex items-center space-x-2">
                                                            {{($this->editAction)(['recordId'=>$competence->id,'type'=>'competence'])}}
                                                            {{($this->attachAction)(['parentId'=>$fonction->id,'parentType'=>'fonction','recordId'=>$competence->id,'type'=>'competence'])}}
                                                          <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                        </div>
                                                      </div>
                                                      @if ($competence->savoirFaires->isNotEmpty())
                                                          <ul class="collapsible">
                                                              @foreach ($competence->savoirFaires as $savoirFaire)
                                                                  <li>
                                                                    <div class="flex items-center justify-between  mt1 mb1">
                                                                      <strong class="blue">Savoir-faire : {{ $savoirFaire->libelle_long }}</strong>
                                                                      <div class="flex items-center space-x-2">
                                                                        {{($this->editAction)(['recordId'=>$savoirFaire->id,'type'=>'savoirFaire'])}}
                                                                    <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                                </div>
                                                                  </div>
                                                                    @if ($savoirFaire->activites->isNotEmpty())
                                                                    <ul class="collapsible">
                                                                        @foreach ($savoirFaire->activites as $activite)
                                                                            <li>
                                                                                <div class="flex items-center justify-between  mt1 mb1">
                                                                                Activite : {{ $activite->libelle_long }}
                                                                            <div class="flex items-center space-x-2">
                                                                                {{($this->editAction)(['recordId'=>$activite->id,'type'=>'activite'])}}
                                                                            </div>
                                                                        </div>
                                                                        </li>
                                                                        @endforeach
                                                                        <li>{{($this->createAction)(['parentId'=>$savoirFaire->id,'parentType'=>'Savoirfaire','type'=>'activite'])}}</li>
                                                                    </ul>
                                                                @endif
                                                                  
                                                                  </li>
                                                              @endforeach
                                                              <li> {{($this->createAction)(['parentId'=>$competence->id,'parentType'=>'competence','type'=>'savoirFaire'])}}</li>
                                                          </ul>
                                                      @endif
                                                  </li>
                                              @endforeach
                                              <li>{{($this->createAction)(['parentId'=>$fonction->id,'parentType'=>'fonction','type'=>'competence'])}}</li>
                                          </ul>
                                      @endif
                                  </li>
                              @endforeach
                              <li>{{($this->createAction)(['parentId'=>$parcours->id,'parentType'=>'parcours','type'=>'fonction'])}}</li>
                          </ul>
                      @endif
                  </li>
              </ul>
          @else
              <p>Aucun parcours trouvé.</p>
          @endif
      </div>
      
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

      </style>
      
      <script>
          function toggleCollapse(button) {
              const collapsible = button.parentElement.parentElement.nextElementSibling;
      
              if (collapsible && collapsible.classList.contains('collapsible')) {
                  if (collapsible.style.display === 'none' || collapsible.style.display === '') {
                      collapsible.style.display = 'block';
                      button.textContent = '[-]'; // Change le bouton pour indiquer "Réduire"
                  } else {
                      collapsible.style.display = 'none';
                      button.textContent = '[+]'; // Change le bouton pour indiquer "Développer"
                  }
              }
          }
      </script>
      
      

    </div>
</x-filament::page>
