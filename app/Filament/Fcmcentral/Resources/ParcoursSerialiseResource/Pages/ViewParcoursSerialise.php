<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Filament\Actions;
use Modules\FcmCentral\Filament\fcmcentral\Resources\ParcoursResource\RelationManagers\MarinsRelationManager; 


class ViewParcoursSerialise extends ViewRecord
{
    protected static ?string $title = 'Détail du PPA';
    protected static string $resource = ParcoursSerialiseResource::class;
    
   // Vue personnalisée
   protected static string  $view = 'fcmcentral::filament.fcmcentral.resources.parcoursserialise-resource.pages.view-parcoursserialise';
   
   public $parcoursserialise;
   public $parcours;

   public function mount($record): void
   {
        parent::mount($record);

        // Charger le parcours complet avec ses relations
       $this->parcoursserialise = ParcoursSerialise::find($record);
       $this->parcours = $this->parcoursserialise->parcours;
    //    dd($this->parcours);
    }

    

    
}