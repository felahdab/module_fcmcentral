<x-filament-panels::page>

<x-filament::section class="mb-5">
    <div class="flex items-start gap-3 text-sm text-gray-500 dark:text-gray-400">
        <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0 text-gray-400" />
        <p>
            Liste de tous les marins ayant au moins un mentor attribué. Une unité en rouge signale que le
            mentor n'est pas dans la même unité que le marin supervisé — pensez à vérifier si le mentorat
            doit être réaffecté.
        </p>
    </div>
</x-filament::section>

{{ $this->table }}

<x-filament-actions::modals />
</x-filament-panels::page>


