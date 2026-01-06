
    <div class="space-y-4">
        <div class="tree-view">
            @if ($parcours)
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold yellow mt1 mb1">{{ $parcours['libelle_long'] }}</h1>
                </div>

                <ul>
                    <li>
                        @if (!empty($parcours['affectations']))
                            <ul>
                                @foreach ($parcours['affectations'] as $affectation)
                                    <li>
                                        {{-- header affectation --}}
                                        <div class="flex items-center mt1 mb1">
                                            <div class="flex items-center space-x-2">
                                                <button type="button" class="toggle-btn" aria-expanded="false">[+]</button>
                                            </div>
                                            <strong class="vert" style="color: {{ $affectation['color'] ?? '#389D60' }}">&nbsp;&nbsp;
                                                {{ $affectation['libelle_long'] }}
                                                <span class="badge-count">({{ count($affectation['competences'] ?? []) }} C)</span>
                                            </strong>
                                        </div>

                                        {{-- collapsible competences --}}
                                        <ul class="collapsible" style="display:none;">
                                            @if (!empty($affectation['competences']))
                                                @foreach ($affectation['competences'] as $competence)
                                                    <li>
                                                        {{-- header competence --}}
                                                        <div class="flex items-center mt1 mb1">
                                                            <div class="flex items-center space-x-2">
                                                                <button type="button" class="toggle-btn" aria-expanded="false">[+]</button>
                                                            </div>
                                                            <strong class="vert" style="color: {{ $competence['color'] ?? '#2b6cb0' }}">&nbsp;&nbsp;
                                                                {{ $competence['code'] ?? '' }} - {{ $competence['libelle_long'] ?? $competence['libelle_court'] ?? '' }}
                                                                <span class="badge-count">({{ count($competence['competenceSavoirfaires'] ?? []) }} SF)</span>
                                                            </strong>
                                                        </div>

                                                        {{-- collapsible competenceSavoirfaires --}}
                                                        <ul class="collapsible" style="display:none;">
                                                            @if (!empty($competence['competenceSavoirfaires']))
                                                                @foreach ($competence['competenceSavoirfaires'] as $csf)
                                                                    <li>
                                                                        {{-- header csf --}}
                                                                        <div class="flex items-center mt1 mb1">
                                                                            <div class="flex items-center space-x-2">
                                                                                {{-- only show toggle if there are objectifs --}}
                                                                                @if (!empty($csf['objectifs']))
                                                                                    <button type="button" class="toggle-btn" aria-expanded="false">[+]</button>
                                                                                @else
                                                                                    <span class="toggle-placeholder">&nbsp;&nbsp;&nbsp;</span>
                                                                                @endif
                                                                            </div>

                                                                            <strong class="bleu" style="color: {{ $csf['savoirfaire']['color'] ?? '#6AADE8' }}">&nbsp;&nbsp;
                                                                                {{ $csf['savoirfaire']['code'] ?? '' }} - {{ $csf['savoirfaire']['libelle_long'] ?? $csf['savoirfaire']['libelle_court'] ?? '' }}
                                                                                @if(!empty($csf['libelle_court']))
                                                                                    <em>({{ $csf['libelle_court'] }})</em>
                                                                                @endif
                                                                                @if(!empty($csf['savoirfaire_type']['libelle_court']))
                                                                                    <span class="type-badge">{{ $csf['savoirfaire_type']['libelle_court'] }}</span>
                                                                                @elseif(!empty($csf['savoirfaire_type']))
                                                                                    <span class="type-badge">{{ $csf['savoirfaire_type'] }}</span>
                                                                                @endif
                                                                            </strong>

                                                                            <div class="sf-details">
                                                                                @if(!empty($csf['heures']))
                                                                                    <span class="detail-item">{{ $csf['heures'] }}h</span>
                                                                                @endif
                                                                                @if(!empty($csf['jours']))
                                                                                    <span class="detail-item">{{ $csf['jours'] }}j</span>
                                                                                @endif
                                                                                @if(!empty($csf['savoirfaire']['competence_code']))
                                                                                    <span class="detail-item">{{ $csf['savoirfaire']['competence_code'] }}</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>

                                                                        {{-- collapsible objectifs --}}
                                                                        <ul class="collapsible" style="display:none;">
                                                                            @if (!empty($csf['objectifs']))
                                                                                @foreach ($csf['objectifs'] as $objectif)
                                                                                    <li>
                                                                                        <div class="flex items-center mt1 mb1">
                                                                                            <strong class="orange">&nbsp;&nbsp;📎 {{ $objectif['code'] ?? '' }} - {{ $objectif['libelle_court'] ?? '' }}</strong>
                                                                                            @if(!empty($objectif['description']))
                                                                                                <div class="objectif-description">{{ \Illuminate\Support\Str::limit($objectif['description'], 100) }}</div>
                                                                                            @endif
                                                                                        </div>
                                                                                    </li>
                                                                                @endforeach
                                                                            @else
                                                                                <li><div class="text-xs text-gray-500 italic ml-8">Aucun objectif</div></li>
                                                                            @endif
                                                                        </ul>
                                                                    </li>
                                                                @endforeach
                                                            @else
                                                                <li><div class="text-xs text-gray-500 italic ml-8">Aucune liaison savoir faire pour cette compétence</div></li>
                                                            @endif
                                                        </ul>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li><div class="text-xs text-gray-500 italic ml-8">Aucune compétence attachée</div></li>
                                            @endif
                                        </ul>
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
        .tree-view ul { list-style-type: none; margin: 0; padding: 0; }
        .tree-view ul ul { margin-left: 20px; border-left: 1px solid #ccc; padding-left: 10px; }
        .badge-count { font-size: 0.8em; color: #666; font-weight: normal; margin-left: 8px; }
        .type-badge { display:inline-block; background:#f3f4f6; color:#374151; padding:2px 6px; border-radius:4px; font-size:0.75em; margin-left:8px; }
        .sf-details { margin-left: 20px; margin-top: 5px; }
        .detail-item { display:inline-block; background:#e5e7eb; color:#4b5563; padding:2px 6px; margin-right:8px; border-radius:3px; font-size:0.8em; }
        .objectif-description { font-size:0.9em; color:#6b7280; margin-top:4px; font-style:italic; margin-left:20px; }
        .toggle-placeholder { width:20px; display:inline-block; }
        .tree-view .collapsible { display: none; }
        .tree-view .toggle-btn { background:none; border:none; color:#D81B60; cursor:pointer; font-size:14px; width:20px; padding:0; }
        .mt1{ margin-top:15px } .mb1{ margin-bottom:15px } .ml-8{ margin-left:2rem }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function toggleHandler(e) {
                const btn = e.currentTarget;
                // header container is the button's grandparent in our markup
                let header = btn.closest('div');
                // find the next sibling that is a collapsible (we ensured collapsible follows header)
                let next = header.nextElementSibling;
                // if not collapsible directly, try a couple of parent levels
                let attempts = 0;
                while (next && !next.classList.contains('collapsible') && attempts < 6) {
                    header = header.parentElement;
                    next = header ? header.nextElementSibling : null;
                    attempts++;
                }
                if (!next || !next.classList.contains('collapsible')) return;
                const isHidden = !next.style.display || next.style.display === 'none';
                next.style.display = isHidden ? 'block' : 'none';
                btn.textContent = isHidden ? '[-]' : '[+]';
                btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            }

            document.querySelectorAll('.toggle-btn').forEach(function (b) {
                b.addEventListener('click', toggleHandler);
            });
        });
    </script>

