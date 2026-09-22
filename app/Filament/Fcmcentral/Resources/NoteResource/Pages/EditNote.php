<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    // Le query string de l'URL initiale n'est plus disponible lors des requêtes Livewire
    // suivantes (ex: au clic sur "Enregistrer"), donc on le mémorise dès le mount().
    public ?string $marinId = null;

    public function mount(int | string $record): void
    {
        $this->marinId = request()->query('marin_id');

        parent::mount($record);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('fcmcentral::note.modifier'), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        if ($this->marinId) {
            return FicheBilan::getUrl(['record' => $this->marinId]);
        }

        return parent::getRedirectUrl();
    }
}
