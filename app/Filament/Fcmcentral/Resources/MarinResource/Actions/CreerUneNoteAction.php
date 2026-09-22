<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;

class CreerUneNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->iconButton()
            ->icon('heroicon-m-newspaper')
            ->color('danger')
            ->extraAttributes([
                'title' => 'Créer une note',
                'class' => 'btn-note',
            ])
            ->url(fn ($record) => NoteResource::getUrl('create', ['marin_id' => $record->id]))
        ;
    }
}
