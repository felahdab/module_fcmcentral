<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource;

class ListStoredEvents extends ListRecords
{
    protected static string $resource = StoredEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
