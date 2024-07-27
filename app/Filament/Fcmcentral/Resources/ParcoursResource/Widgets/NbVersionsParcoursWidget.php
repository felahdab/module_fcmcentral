<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

class NbVersionsParcoursWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $parcoursserialises = ParcoursSerialise::where('uuid', $this->record->id)->get();
        $nbversions=$parcoursserialises->count();
        
        $nbmarins=UserParcours::whereIn('parcours_id', $parcoursserialises->pluck('id'))->count();

        return [
            Stat::make('Nombre de versions figées de ce parcours', $nbversions),
            Stat::make('Nombre de marins ayant ce parcours attribué', $nbmarins),
        ];
    }
}
