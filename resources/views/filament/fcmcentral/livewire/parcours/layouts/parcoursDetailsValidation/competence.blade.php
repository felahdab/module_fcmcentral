@props(['parcoursId', 'competence', 'openSections'])

<li>
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'competences',
        'id'            => $competence['id'],
        'label'         => 'Competence : ' . $competence['libelle_long'],
        'libelleLong'   => $competence['libelle_long'],
        'libelleCourt'  => $competence['libelle_court'],
        'etatValid'     => $competence['etat_valid'],
        'dateValidation'=> $competence['date_validation'],
        'dateProposition'=> $competence['date_proposition'],
        'valideur'      => $competence['valideur'],
        'commentaire'   => $competence['commentaire'],
        'commentaireProposition' => $competence['commentaire_proposition'],
        'finDeCourse'   => empty($competence['savoirfaires']),
        'infos'         => [],
    ])

    @if (!empty($competence['savoirfaires']))
        <ul class="collapsible highlight" style="display:{{ isset($openSections['competences_'.$competence['id']]) ? 'block' : 'none' }}">
            @foreach ($competence['savoirfaires'] as $savoirFaire)
                @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.savoirfaire', [
                    'parcoursId' => $parcoursId,
                    'savoirFaire' => $savoirFaire,
                    'openSections' => $openSections,
                ])
            @endforeach
        </ul>
    @endif
</li>
