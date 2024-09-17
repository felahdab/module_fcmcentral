<x-filament-panels::page>
    <form>
        {{ $this->form }}
        
    </form>

    {{ $this->validateConfigurationAction() }}

    
    <x-filament-actions::modals />
</x-filament-panels::page>
