<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentations extends ListRecords
{
    protected static string $resource = DocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
