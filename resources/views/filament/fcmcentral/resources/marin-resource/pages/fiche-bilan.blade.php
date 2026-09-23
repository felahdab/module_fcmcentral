<x-filament-panels::page>

@include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.styles')

{{-- VÉRIFICATION MARIN --}}
@if(!$this->marin)
    <x-filament::section>
        <div class="text-center py-8">
            <div class="text-red-600 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Marin introuvable</h3>
            <p class="text-gray-500">Le marin avec l'ID {{ $marinId }} n'existe pas.</p>
        </div>
    </x-filament::section>
@else

    @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.alerte-gel')
    @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.en-tete')

    {{-- VÉRIFICATION PARCOURS --}}
    @if(!$this->parcours)
        <x-filament::section>
            <div class="text-center py-8">
                <div class="text-yellow-600 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun parcours assigné</h3>
                <p class="text-gray-500">Ce marin n'a pas encore été affecté à un parcours FCM.</p>
            </div>
        </x-filament::section>
    @else

        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.informations')

        {{-- SECTIONS 2 COLONNES --}}
        <div class="grid-sections-2 mb-6">
            @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.savoir-faire')
            @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.notes')
        </div>

        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.statistiques')

        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.documents-annexes')
        @include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.objectifs-attente')

    @endif
@endif

@include('fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan.modals')

</x-filament-panels::page>
