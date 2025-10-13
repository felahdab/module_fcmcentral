<div class="space-y-4">
    <div class="tree-view">
        @if ($parcours)
            <div class="flex items-center">
                <h1 class="text-2xl font-bold yellow mt1 mb1">
                    {{ $parcours['libelle_long'] }}
                </h1>
            </div>

            <ul>
                <li>
                    @if ($parcours['annees'])
                        <ul>
                            @foreach ($parcours['annees'] as $annee)
                                <li>
                                    <div class="flex items-center mt1 mb1">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                        </div>        
                                        <strong class="vert" style="color: {{ $annee['color'] ?? '#389D60' }}">&nbsp;&nbsp;
                                            {{ $annee['libelle_long'] }}
                                            <span class="badge-count">({{ count($annee['savoirfaires'] ?? []) }} SF)</span>
                                        </strong>                              
                                    </div>

                                    @if ($annee['savoirfaires'])
                                        <ul class="collapsible">
                                            @foreach ($annee['savoirfaires'] as $savoirfaire)
                                                <li>
                                                    <div class="flex items-center mt1 mb1">
                                                        <div class="flex items-center space-x-2">
                                                            @if(!empty($savoirfaire['objectifs']))
                                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                            @else
                                                                <span class="toggle-placeholder">&nbsp;&nbsp;&nbsp;</span>
                                                            @endif
                                                        </div>
                                                        <strong class="bleu" style="color: {{ $savoirfaire['color'] ?? '#6AADE8' }}">&nbsp;&nbsp;
                                                            {{ $savoirfaire['code'] }} - {{ $savoirfaire['libelle_long'] }}
                                                            @if(!empty($savoirfaire['intitule']))
                                                                <em>({{ $savoirfaire['intitule'] }})</em>
                                                            @endif
                                                            @if(!empty($savoirfaire['savoirfaire_type']))
                                                                <span class="type-badge">{{ $savoirfaire['savoirfaire_type'] }}</span>
                                                            @endif
                                                        </strong>
                                                       
                                                        {{-- Détails du savoir-faire --}}
                                                        <div class="sf-details">
                                                            @if(!empty($savoirfaire['heures']))
                                                                <span class="detail-item">{{ $savoirfaire['heures'] }}h</span>
                                                            @endif
                                                            @if(!empty($savoirfaire['jours']))
                                                                <span class="detail-item">{{ $savoirfaire['jours'] }}j</span>
                                                            @endif
                                                            @if(!empty($savoirfaire['competence_code']))
                                                                <span class="detail-item">{{ $savoirfaire['competence_code'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if (!empty($savoirfaire['objectifs']))
                                                        <ul class="collapsible">
                                                            @foreach ($savoirfaire['objectifs'] as $objectif)
                                                                <li>
                                                                    <div class="flex items-center mt1 mb1">
                                                                        <strong class="orange">&nbsp;&nbsp;
                                                                            📎 {{ $objectif['libelle_long'] }}
                                                                        </strong>
                                                                        @if(!empty($objectif['description']))
                                                                            <div class="objectif-description">
                                                                                {{ Str::limit($objectif['description'], 100) }}
                                                                            </div>
                                                                        @endif
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

        .bleu { color: #6AADE8; }
        .vert { color: #389D60; }
        .orange { color: #e67b47; }

        .badge-count {
            font-size: 0.8em;
            color: #666;
            font-weight: normal;
            margin-left: 8px;
        }

        .type-badge {
            display: inline-block;
            background: #f3f4f6;
            color: #374151;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75em;
            margin-left: 8px;
        }

        .sf-details {
            margin-left: 20px;
            margin-top: 5px;
        }

        .detail-item {
            display: inline-block;
            background: #e5e7eb;
            color: #4b5563;
            padding: 2px 6px;
            margin-right: 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }

        .objectif-description {
            font-size: 0.9em;
            color: #6b7280;
            margin-top: 4px;
            font-style: italic;
            margin-left: 20px;
        }

        .toggle-placeholder {
            width: 20px;
            display: inline-block;
        }

        .tree-view .collapsible {
            display: none;
        }

        .tree-view .toggle-btn {
            background: none;
            border: none;
            color: #D81B60;
            cursor: pointer;
            font-size: 14px;
            width: 20px;
        }

        .tree-view .toggle-btn:focus {
            outline: none;
        }

        .mt1 { margin-top: 15px; }
        .mb1 { margin-bottom: 15px; }
        .mr1 { margin-right: 15px; }
        .ml1 { margin-left: 15px; }
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
</div>
