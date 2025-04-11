<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\FcmMarinResource;
use Filament\Actions;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Models\ParcoursSerialise;

class ViewFcmMarin extends ViewRecord
{
    protected static string $resource = FcmMarinResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    

    
}
