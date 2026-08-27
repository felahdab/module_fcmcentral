<x-filament-panels::page>
    {{ $this->form }}

    <div class="mt-6">
        <x-filament::button wire:click="save" wire:loading.attr="disabled" wire:target="save,upload">
            Enregistrer
        </x-filament::button>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
