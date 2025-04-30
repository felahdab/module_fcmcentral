<x-filament::card>


    <h2 class="text-xl font-bold mb-4">Parcours Sérialisé</h2>
    @if (!empty($parcours))
        <form>
            <h2 class="text-xl font-bold mb-4">Parcours à valider</h2>

            @foreach ($parcours as $parcoursItem)
                <h4 class="text-md font-bold">{{ $parcoursItem['libelle_court'] }}</h4>
                <p class="text-sm">Taux Global : {{ $parcoursItem['taux_global'] ?? 'N/A' }}</p>
                <p class="text-sm">Taux Stage : {{ $parcoursItem['taux_stage'] ?? 'N/A' }}</p>
                <p class="text-sm">Taux Activité : {{ $parcoursItem['taux_activite'] ?? 'N/A' }}</p>
                <p class="text-sm">Détails du parcours :</p>

                <h3 class="text-2xl font-bold yellow mt1 mb1">Parcours : {{ $parcoursItem['parcours']['libelle_long'] }}</h3>

                <ul>
                    <li>
                        @if ($parcoursItem['parcours']['fonctions'])
                            <ul>
                                @foreach ($parcoursItem['parcours']['fonctions'] as $indexFonction => $fonction)
                                    <li>
                                        <div class="flex items-center justify-between mt1 mb1">
                                            <label>
                                            <input type="checkbox" wire:model="selectedFonctions.{{ $parcoursItem['id'] }}.{{ $indexFonction }}" />
                                            <strong class="greenyellow">Fonction : {{ $fonction['libelle_long'] }}</strong>
                                            </label>
                                            <div class="flex items-center space-x-2">
                                               
                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                            </div>
                                        </div>
                                        @if ($fonction['competences'])
                                            <ul class="collapsible ml1">
                                                @foreach ($fonction['competences'] as $indexCompetence => $competence)
                                                    <li>
                                                        <div class="flex items-center justify-between mt1 mb1">
                                                            <label>
                                                                <input type="checkbox" wire:model="selectedCompetences.{{ $parcoursItem['id'] }}.{{ $indexFonction }}.{{ $indexCompetence }}" />
                                                            <strong class="aquamarine">Compétence : {{ $competence['libelle_long'] }}</strong>
                                                            </label>
                                                            <div class="flex items-center space-x-2">
                                                                
                                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                            </div>
                                                        </div>
                                                        @if ($competence['savoirfaires'])
                                                            <ul class="collapsible ml1">
                                                                @foreach ($competence['savoirfaires'] as $indexSavoirfaire => $savoirFaire)
                                                                    <li>
                                                                        <div class="flex items-center justify-between mt1 mb1">
                                                                            <label>
                                                                                <input type="checkbox" wire:model="selectedSavoirFaires.{{ $parcoursItem['id'] }}.{{ $indexFonction }}.{{ $indexCompetence }}.{{ $indexSavoirfaire }}" />
                                                                            <strong class="blue">Savoir-faire : {{ $savoirFaire['libelle_long'] }}</strong>
                                                                            </label>
                                                                            <div class="flex items-center space-x-2">
                                                                             
                                                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                                            </div>
                                                                        </div>
                                                                        @if ($savoirFaire['activites'])
                                                                            <ul class="collapsible ml1">
                                                                                @foreach ($savoirFaire['activites'] as $indexActivite => $activite)
                                                                                    <li>
                                                                                        <div class="flex items-center justify-between mt1 mb1">
                                                                                            <label >
                                                                                                <input type="checkbox" wire:model="selectedActivites.{{ $parcoursItem['id'] }}.{{ $indexFonction }}.{{ $indexCompetence }}.{{ $indexSavoirfaire }}.{{ $indexActivite }}" />
                                                                                            Activité : {{ $activite['libelle_long'] }}
                                                                                            </label>
                                                                                            <div class="flex items-center space-x-2">
                                                                                               
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
        <button type="button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" wire:click="$dispatch('close-modal',{id:'comment'})">Annuler</button>
        <button type="button" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" wire:click="saveForm">Valider</button>
    </x-slot>
</x-filament::modal>



    <style>
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
                    button.textContent = '[-]';
                } else {
                    collapsible.style.display = 'none';
                    button.textContent = '[+]';
                }
            }
        }
    </script>
</x-filament::card>
