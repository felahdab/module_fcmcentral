<div class="fi-fo-component-ctn">

    @if($marin)
        <x-filament::section :compact="true" class="mb-6">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-user" class="w-5 h-5 text-primary-600" />
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $marin->grade?->libelle_court }} {{ $marin->prenom }} {{ $marin->nom }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">NID: {{ $marin->nid }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif

    <form wire:submit="save" class="space-y-6">
        
        {{-- Type d'état --}}
        <div>
            <label class="text-sm text-gray-500 dark:text-gray-400 p-2">
                Type d'État <span class="text-red-500">*</span>
            </label>
            
            <x-filament::input.wrapper
                :valid="! $errors->has('typeEtatId')"
                class="space-y-2"
            >
                <x-filament::input.select
                    wire:model.live="typeEtatId"
                    id="typeEtatId"
                    required
                >
                    <option value="">Sélectionnez un type</option>
                    @foreach($typesEtats as $type)
                        <option value="{{ $type->id }}">{{ $type->libelle_court }}</option>
                    @endforeach
                </x-filament::input.select>
                
                @error('typeEtatId')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Date de début --}}
        <div>
            <x-filament::input.wrapper
                :valid="! $errors->has('dateDebut')"
                class="space-y-2"
            >
                <x-slot name="label">
                    Date de début <span class="text-red-500">*</span>
                </x-slot>
                
                <x-filament::input
                    type="date"
                    wire:model.live="dateDebut"
                    id="dateDebut"
                    required
                />
                
                @error('dateDebut')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @else
                    <x-slot name="helper-text">
                        <span class="text-gray-500 dark:text-gray-400">
                            @if($etatId)
                                Modification de la date de début
                            @else
                                Cette date clôturera l'état en cours si existant
                            @endif
                        </span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Date de fin (optionnelle) --}}
        <div>
            <x-filament::input.wrapper
                :valid="! $errors->has('dateFin')"
                class="space-y-2"
            >
                <x-slot name="label">
                    Date de fin (optionnel)
                </x-slot>
                
                <x-filament::input
                    type="date"
                    wire:model.live="dateFin"
                    id="dateFin"
                />
                
                @error('dateFin')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @else
                    <x-slot name="helper-text">
                        <span class="text-gray-500 dark:text-gray-400">Laisser vide si l'état est en cours</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Boutons d'action --}}
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <x-filament::button
                color="gray"
                wire:click="cancel"
                size="sm"
            >
                Annuler
            </x-filament::button>

            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                size="sm"
            >
                <x-filament::loading-indicator
                    wire:loading
                    wire:target="save"
                    class="h-4 w-4"
                />
                <span wire:loading.remove wire:target="save">
                    {{ $etatId ? 'Modifier l\'état' : 'Ajouter l\'état' }}
                </span>
                <span wire:loading wire:target="save">Enregistrement...</span>
            </x-filament::button>
        </div>
    </form>
</div>
