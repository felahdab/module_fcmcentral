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
                    <div class="font-semibold text-gray-900">
                        {{ $marin->grade?->libelle_court }} {{ $marin->prenom }} {{ $marin->nom }}
                    </div>
                    <div class="text-sm text-gray-500">NID: {{ $marin->nid }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif

    <form wire:submit="save" class="space-y-6">
       
        {{-- Type de note --}}
        <div>
            <label class="text-sm text-gray-500 p-2">
                Type de Note <span class="text-red-500">*</span>
            </label>
           
            <x-filament::input.wrapper
                :valid="! $errors->has('typeNote')"
                class="space-y-2"
            >
                <x-filament::input.select
                    wire:model="typeNote"
                    id="typeNote"
                    required
                >
                    <option value="">Sélectionnez un type</option>
                    @foreach($typesNotes as $type)
                        <option value="{{ $type->id }}">{{ $type->libelle_court }}</option>
                    @endforeach
                </x-filament::input.select>
               
                @error('typeNote')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- ✅ RÉDACTEUR --}}
        {{-- <div>
            <label class="text-sm text-gray-500 p-2">
                Rédacteur <span class="text-red-500">*</span>
            </label>
           
            <x-filament::input.wrapper
                :valid="! $errors->has('redacteurId')"
                class="space-y-2"
            >
                <x-filament::input.select
                    wire:model="redacteurId"
                    id="redacteurId"
                    required
                >
                    <option value="">Sélectionnez un rédacteur</option>
                    @foreach($redacteurs as $id => $nom)
                        <option value="{{ $id }}">{{ $nom }}</option>
                    @endforeach
                </x-filament::input.select>
               
                @error('redacteurId')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @else
                    <x-slot name="helper-text">
                        <span class="text-gray-500">Qui rédige cette note ?</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div> --}}

        {{-- Date --}}
        <div>
            <x-filament::input.wrapper
                :valid="! $errors->has('date')"
                class="space-y-2"
            >
                <x-slot name="label">
                    Date <span class="text-red-500">*</span>
                </x-slot>
               
                <x-filament::input
                    type="date"
                    wire:model="date"
                    id="date"
                    max="{{ now()->format('Y-m-d') }}"
                    required
                />
               
                @error('date')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Contenu --}}
        <div>
            <x-filament::input.wrapper
                :valid="! $errors->has('libelle')"
                class="space-y-2"
            >
                <x-slot name="label">
                    Contenu de la note <span class="text-red-500">*</span>
                </x-slot>
               
                <textarea
                    wire:model="libelle"
                    id="libelle"
                    rows="4"
                    placeholder="Saisissez le contenu de la note..."
                    required
                    class="fi-input block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500"
                ></textarea>
               
                @error('libelle')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @else
                    <x-slot name="helper-text">
                        <span class="text-gray-500">Minimum 10 caractères - {{ strlen($libelle ?? '') }}/1000</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Lien URL --}}
        <div>
            <x-filament::input.wrapper
                :valid="! $errors->has('lienUrl')"
                class="space-y-2"
            >
                <x-slot name="label">
                    Lien URL (optionnel)
                </x-slot>
               
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        https://
                    </span>
                    <x-filament::input
                        type="text"
                        wire:model="lienUrl"
                        id="lienUrl"
                        placeholder="exemple.com/document"
                        class="rounded-l-none"
                    />
                </div>
               
                @error('lienUrl')
                    <x-slot name="helper-text">
                        <span class="text-red-600">{{ $message }}</span>
                    </x-slot>
                @enderror
            </x-filament::input.wrapper>
        </div>

        {{-- Boutons d'action --}}
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
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
                <span wire:loading.remove wire:target="save">Ajouter la note</span>
                <span wire:loading wire:target="save">Ajout en cours...</span>
            </x-filament::button>
        </div>
    </form>
</div>
