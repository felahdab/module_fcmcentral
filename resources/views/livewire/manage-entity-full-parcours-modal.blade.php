<div>
  {{ $action}}
  <h2>{{ $action === 'edit' ? 'Modifier' : 'Ajouter' }} {{ ucfirst($type) }}</h2>
        
  <form wire:submit.prevent="save">
      <input type="text" wire:model="libelle_long" placeholder="Nom">
      @error('libelle_long') <span class="error">{{ $message }}</span> @enderror

      <button type="submit">Sauvegarder</button>
      <button type="button" x-on:click="open = false">Fermer</button>
  </form>

