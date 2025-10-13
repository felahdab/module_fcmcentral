<x-filament::page>
    <div class="space-y-4">
        <div class="tree-view">
            @if ($parcours)              
                <div class="flex items-center justify-between text-2xl font-bold mt1 mb1">
                    {{ $parcours->libelle_long }}
                </div>
                <ul>
                    <li>
                        @if ($parcours->annees->isNotEmpty())
                            <ul>
                                @foreach ($parcours->annees->sortBy('pivot.ordre') as $annee)
                                    <li>
                                        <div class="flex items-center mt1 mb1">
                                            <div class="flex items-center">
                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                            </div>
                                            <strong class="vert" style="color: {{ $annee->color ?? '#389D60' }}">&nbsp;&nbsp;
                                                {{ $annee->libelle_long }}
                                                <span class="badge-count">({{ $annee->anneeSavoirfaires->count() }} SF)</span>
                                            </strong>
                                        </div>

                                        @if ($annee->anneeSavoirfaires->isNotEmpty())
                                            <ul class="collapsible">
                                                @foreach ($annee->anneeSavoirfaires->sortBy('ordre') as $anneeSavoirfaire)
                                                    <li>
                                                        <div class="flex items-center mt1 mb1">
                                                            <div class="flex items-center space-x-2">
                                                                @if($anneeSavoirfaire->savoirfaireType->code == 'C')
                                                                <button type="button" class="toggle-btn" onclick="toggleCollapse(this)">[+]</button>
                                                                @endif
                                                            </div>
                                                            <strong class="bleu" style="color: {{ $anneeSavoirfaire->savoirfaire->color ?? $anneeSavoirfaire->savoirfaire->competence->color ?? '#6AADE8' }}">&nbsp;&nbsp;
                                                                {{ $anneeSavoirfaire->savoirfaire->code }} - {{ Str::limit($anneeSavoirfaire->savoirfaire->libelle_long, 60) }}
                                                                @if($anneeSavoirfaire->libelle_court)
                                                                    <em>({{ $anneeSavoirfaire->libelle_court }})</em>
                                                                @endif
                                                                @if($anneeSavoirfaire->savoirfaireType)
                                                                    <span class="type-badge">{{ $anneeSavoirfaire->savoirfaireType->libelle_court }}</span>
                                                                @endif
                                                            </strong>
                                                            <div class="sf-details">
                                                                @if($anneeSavoirfaire->heures)
                                                                    <span class="detail-item">{{ $anneeSavoirfaire->heures }}h</span>
                                                                @endif
                                                                @if($anneeSavoirfaire->jours)
                                                                    <span class="detail-item">{{ $anneeSavoirfaire->jours }}j</span>
                                                                @endif
                                                                {{-- <span class="detail-item">Ordre: {{ $anneeSavoirfaire->ordre }}</span> --}}
                                                            </div>
                                                        </div>

                                                        @if ($anneeSavoirfaire->objectifs->isNotEmpty())
                                                            <ul class="collapsible">
                                                                @foreach ($anneeSavoirfaire->objectifs as $objectif)
                                                                    <li>
                                                                        <div class="flex items-center mt1 mb1">
                                                                            <strong class="orange">&nbsp;&nbsp;
                                                                                📎 {{ $objectif->libelle_court  }}
                                                                            </strong>
                                                                            @if($objectif->description)
                                                                                <div class="objectif-description">
                                                                                    {{ Str::limit($objectif->libelle_court, 100) }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        {{-- @else
                                                            <ul class="collapsible">
                                                                <li>
                                                                    <em class="no-objectifs">Aucun objectif défini</em>
                                                                </li>
                                                            </ul> --}}
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
        }

        .no-objectifs {
            color: #9ca3af;
            font-size: 0.9em;
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
</x-filament::page>
