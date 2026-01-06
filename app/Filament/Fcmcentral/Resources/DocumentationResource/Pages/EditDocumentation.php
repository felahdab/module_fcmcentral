<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentation extends EditRecord
{
    protected static string $resource = DocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
