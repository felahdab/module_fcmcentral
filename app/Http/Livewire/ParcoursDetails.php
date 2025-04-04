<?php

namespace Modules\FcmCentral\Http\Livewire;

use Modules\FcmCentral\Models\ParcoursSerialise;

use Livewire\Component;

class ParcoursDetails extends Component
{
    
    // public ?string $uuid ;
    public array|null $parcours=[];

    public function mount($parcours)
    {
        $this->parcours = is_string($parcours) ? json_decode($parcours, true) : $parcours;
        //$this->parcours = json_encode($parcours, JSON_PRETTY_PRINT) ;
        // $parcoursSerialise = ParcoursSerialise::find(['uuid'=>$uuid]);


        // $this->parcours= $parcoursSerialise->parcours;


    }
    
    
    public function render()
    {
        return view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details');
    }
}
