<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Filament\Notifications\Notification;
use Livewire\Attributes\Computed;
use Modules\FcmCentral\Models\Note;

trait HasNotes
{
    public function canCreateNote(): bool
    {
        return auth()->user()->can('fcmcentral::note.creer');
    }

    public function canViewNotes(): bool
    {
        return auth()->user()->can('fcmcentral::note.voir');
    }

    #[Computed]
    public function notes()
    {
        // ✅ SEULS BCF/FPS/BORD/ADMIN voient les notes
        if (!$this->canViewNotes()) {
            return collect([]);
        }

        $query = Note::where('rh_marin_id', $this->marinId)
            ->with(['typeNote', 'redacteur'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
        ;

        return $query->get();
    }

    public function ajouterNote()
    {
        if (!$this->canCreateNote()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour créer une note')
                ->warning()
                ->send()
            ;

            return;
        }

        $this->dispatch('open-modal', id: 'ajouterNote');
    }

    public function refreshNotes()
    {
        $this->dispatch('$refresh');
    }
}
