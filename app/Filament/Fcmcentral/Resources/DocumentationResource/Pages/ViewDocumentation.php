<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource;

class ViewDocumentation extends ViewRecord
{
    protected static string $resource = DocumentationResource::class;

    // Vérification d'accès simple au mount
    public function mount($record): void
    {
        parent::mount($record);

        // Si tu stockes des droits en JSON dans $record->droits (array de permission names)
        // if (! empty($this->record->droits)) {
        //     $allowed = false;
        //     foreach ($this->record->droits as $perm) {
        //         if (auth()->user() && auth()->user()->can($perm)) {
        //             $allowed = true;
        //             break;
        //         }
        //     }
        //     abort_unless($allowed, 403);
        // }
    }
}
