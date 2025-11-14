<x-filament::page>
    <div class="space-y-4">
        @if ($parcours)
            <div class="text-2xl font-bold mb-4">{{ $parcours->libelle_long }}</div>

            <div class="space-y-3">
                @foreach($parcours->affectations->sortBy('pivot.ordre') as $affectation)
                    <div class="p-3 border rounded bg-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <button type="button" class="toggle-btn text-sm font-medium text-indigo-600">[+]</button>
                                <div>
                                    <div class="font-semibold" style="color: #2b6cb0">
                                        {{ $affectation->libelle_long }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $affectation->competences->count() }} compétence(s)
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">ordre: {{ $affectation->pivot->ordre ?? '-' }}</div>
                        </div>

                        <div class="collapsible mt-3 space-y-2" style="display:none;">
                            @if($affectation->competences->isNotEmpty())
                                @foreach($affectation->competences->sortBy('ordre') as $competence)
                                    <div class="p-2 border rounded bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="toggle-btn text-sm ">[+]</button>
                                                <div>
                                                    <div class="font-medium bleu">
                                                        {{ $competence->code ?? '' }} - {{ $competence->libelle_long }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $competence->competenceSavoirfaires->count() }} liaison(s) SF
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">ordre: {{ $competence->ordre ?? '-' }}</div>
                                        </div>

                                        <div class="collapsible mt-2 ml-8 space-y-2" style="display:none;">
                                            @if($competence->competenceSavoirfaires->isNotEmpty())
                                                @foreach($competence->competenceSavoirfaires->sortBy('ordre') as $csf)
                                                    <div class="p-2 border rounded bg-white">
                                                        <div class="flex items-start justify-between">
                                                            
                                                            <div class="flex items-center gap-3">
                                                                @if($csf->objectifs->isNotEmpty())    
                                                                <button type="button" class="toggle-btn text-sm ">[+]</button>
                                                                @endif
                                                                <div>
                                                                   
                                                                    <div class="text-sm font-medium vert" >
                                                                        {{ $csf->savoirfaire->code ?? '' }} - {{ \Illuminate\Support\Str::limit($csf->savoirfaire->libelle_long, 80) }}
                                                                    </div>
                                                                    <div class="text-xs text-gray-500">
                                                                        {{ $csf->libelle_court ? "intitulé: {$csf->libelle_court}" : '' }}
                                                                        • heures: {{ $csf->heures ?? '—' }} • jours: {{ $csf->jours ?? '—' }}
                                                                        @if($csf->savoirfaireType)
                                                                            • <span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $csf->savoirfaireType->libelle_court }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-xs text-gray-400">ordre: {{ $csf->ordre ?? '-' }}</div>
                                                        </div>

                                                        <div class="collapsible mt-2 ml-8" style="display:none;">
                                                            @if($csf->objectifs->isNotEmpty())
                                                                <ul class="space-y-1">
                                                                    @foreach($csf->objectifs as $objectif)
                                                                        <li class="p-2 bg-gray-50 rounded border">
                                                                            <div class="font-medium text-sm orange">📎 {{ $objectif->libelle_court }}</div>
                                                                            @if(!empty($objectif->description))
                                                                                <div class="text-xs text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($objectif->description, 140) }}</div>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <div class="text-xs text-gray-500 italic">Aucun objectif</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-xs text-gray-500 italic">Aucune liaison savoir faire pour cette compétence</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-xs text-gray-500 italic">Aucune compétence attachée</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div>Aucun parcours trouvé.</div>
        @endif
    </div>

    <style>
        .ml-8{ margin-left: 2rem }
        .mt-3{ margin-top: .75rem }
        .mb-4{ margin-bottom: 1rem }
        .toggle-btn { background: none; border: none; color: #D81B60; cursor: pointer; font-size: 14px; padding:0; }
        .bleu { color: #6AADE8; }
        .vert { color: #389D60; }
        .orange { color: #e67b47; }

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    // trouve l'élément collapsible le plus proche qui suit le container parent
                    let container = btn.closest('div'); // bouton -> son block
                    // cherche le collapsible immédiatement après le container (remonte si nécessaire)
                    let next = container.nextElementSibling;
                    // si next n'est pas collapsible, essaie de remonter jusqu'au parent qui a le collapsible suivant
                    let attempts = 0;
                    while (next && !next.classList.contains('collapsible') && attempts < 5) {
                        container = container.parentElement;
                        next = container ? container.nextElementSibling : null;
                        attempts++;
                    }
                    if (!next || !next.classList.contains('collapsible')) return;
                    if (next.style.display === 'none' || next.style.display === '') {
                        next.style.display = 'block';
                        btn.textContent = '[-]';
                    } else {
                        next.style.display = 'none';
                        btn.textContent = '[+]';
                    }
                });
            });
        });
    </script>
</x-filament::page>

