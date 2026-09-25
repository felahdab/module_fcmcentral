{{-- Statut FCM du marin : sédimentation en alerte, FCM normal, le reste grisé. --}}
@php
    [$libelle, $couleur, $icone] = match ($marin->complementsFcm?->getJalonFcmLibelle()) {
        'SED' => ['En sédimentation', 'danger', 'heroicon-s-exclamation-triangle'],
        'FCM' => ['En FCM', 'success', 'heroicon-s-check-circle'],
        'FIN' => ['FCM terminée', 'gray', 'heroicon-o-minus-circle'],
        default => ['Non assigné', 'gray', 'heroicon-o-minus-circle'],
    };
@endphp
<x-filament::badge :color="$couleur" :icon="$icone" size="lg" class="{{ 'gray' === $couleur ? 'opacity-60' : '' }}">
    {{ $libelle }}
</x-filament::badge>
