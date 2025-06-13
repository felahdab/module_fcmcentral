<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Illuminate\Database\Eloquent\Model;

use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\MarinParcours;

class NbVersionsParcoursWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $parcoursSerialises = ParcoursSerialise::where('parcours_id', $this->record->id)->get();
        $nbversions=$parcoursSerialises->count();
       
        
        $nbmarins=MarinParcours::whereIn('parcoursserialise_id', $parcoursSerialises->pluck('id'))->count();
       

        return [
            Stat::make('Nombre de versions figées de ce parcours', $nbversions),
           Stat::make('Nombre de marins ayant ce parcours attribué', $nbmarins),
        ];
    }
}
