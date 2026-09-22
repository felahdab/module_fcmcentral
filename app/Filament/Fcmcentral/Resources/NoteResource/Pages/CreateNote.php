<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('fcmcentral::note.creer'), 403);
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        // On applique d'abord les valeurs par défaut de chaque champ (date, rédacteur, …),
        // puis on écrase uniquement rh_marin_id si présent dans l'URL, sans effacer le reste.
        $this->form->fill();

        if ($marinId = request()->query('marin_id')) {
            $this->form->fillPartially(['rh_marin_id' => $marinId], ['rh_marin_id']);
        }

        $this->callHook('afterFill');
    }

    protected function getRedirectUrl(): string
    {
        if ($marinId = request()->query('marin_id')) {
            return FicheBilan::getUrl(['record' => $marinId]);
        }

        return parent::getRedirectUrl();
    }
}
