<x-filament-panels::page>
    <form>
        {{ $this->form }}

        {{ $this->validateConfigurationAction() }}
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
