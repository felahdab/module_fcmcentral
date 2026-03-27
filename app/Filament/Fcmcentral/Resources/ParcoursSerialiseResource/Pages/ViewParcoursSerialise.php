<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
use Modules\FcmCentral\Models\ParcoursSerialise;

class ViewParcoursSerialise extends ViewRecord
{
    public $parcoursserialise;
    public $parcours;
    protected static ?string $title = 'Détail du PPA';
    protected static string $resource = ParcoursSerialiseResource::class;

    // Vue personnalisée
    protected string $view = 'fcmcentral::filament.fcmcentral.resources.parcoursserialise-resource.pages.view-parcoursserialise';

    public function mount($record): void
    {
        parent::mount($record);

        // Charger le parcours complet avec ses relations
        $this->parcoursserialise = ParcoursSerialise::find($record);
        $this->parcours = $this->parcoursserialise->parcours;
        //    dd($this->parcours);
    }
}
