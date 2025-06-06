@props(['parcoursId', 'savoirFaire', 'openSections'])

<li>
    @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'savoirfaires',
        'id'            => $savoirFaire['id'],
        'label'         => 'Savoir Faire : ' . $savoirFaire['libelle_long'],
        'libelleLong'   => $savoirFaire['libelle_long'],
        'libelleCourt'  => $savoirFaire['libelle_court'],
        'etatValid'     => $savoirFaire['etat_valid'],
        'dateValidation'=> $savoirFaire['date_validation'],
        'dateProposition'=> $savoirFaire['date_proposition'],
        'valideur'      => $savoirFaire['valideur'],
        'commentaire'   => $savoirFaire['commentaire'],
        'commentaireProposition' => $savoirFaire['commentaire_proposition'],
        'finDeCourse'   => empty($savoirFaire['activites']),
        'infos'         => [
            'coeff' => $savoirFaire['coeff'] ?? null,
            'duree' => $savoirFaire['duree'] ?? null,
        ],
    ])

    @if (!empty($savoirFaire['activites']))
        <ul class="collapsible ml2 mb1">
            @foreach ($savoirFaire['activites'] as $activite)
                @include('fcmcentral::filament.fcmcentral.livewire.parcours.layouts.parcoursDetailsValidation.activite', [
                    'parcoursId' => $parcoursId,
                    'activite' => $activite,
                ])
            @endforeach
        </ul>
    @endif
</li>
