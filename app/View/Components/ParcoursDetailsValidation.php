<?php

namespace Modules\FcmCentral\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ParcoursDetailsValidation extends Component
{
    
    public $parcoursId;
    public $type;
    public $id;
    public $label;
    public $selectedItems;
    public $openSections;

    
    
    /**
     * Create a new component instance.
     */
    public function __construct($parcoursId,$type,$id,$label,$selectedItems=[],$openSections= [])
    {
       
        
        $this->parcoursId = $parcoursId;
        $this->type = $type;
        $this->id = $id ;
        $this->label = $label;
        $this->selectedItems= $selectedItems;
        $this->openSections= $openSections;

    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('fcmcentral::components.parcoursdetailsvalidation');
    }
}
