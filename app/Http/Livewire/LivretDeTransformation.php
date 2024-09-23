<?php

namespace Modules\FcmCentral\Http\Livewire;

use Modules\FcmCommun\Http\Livewire\Livret as BaseLivretDeTransformation;

use Modules\FcmCommun\Services\LivretService;

use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCentral\Models\MarinParcours;


class LivretDeTransformation extends BaseLivretDeTransformation
{
    public function mount($uuid)
    {
        //dump('mounting');
        $this->uuid = $uuid;
        $this->livret = $this->livret_service->get_livret($this->uuid);;
    }

    public function boot()
    {
        //dump('boot');
        $this->livret_service = new LivretService(new ParcoursService(), MarinParcours::class);
    }
}
