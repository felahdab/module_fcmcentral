<?php

namespace Modules\FcmCentral\Http\Livewire;

use Livewire\Component;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Fonction;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\SavoirFaire;
use Modules\FcmCentral\Models\Activite;

class ManageEntityFullParcoursModal extends Component
{
    
    public $action;
    public $type;
    public $record;
    public $parent;
    public $libelle_long;

    protected $rules = [
        'libelle_long' => 'required|string|max:255',
    ];

    public function mount($action, $type, $record = null, $parent = null)
    {
        $this->action = $action;
        $this->type = $type;
        $this->record = $record;
        $this->parent = $parent;

        if ($record) {
            $this->libelle_long = $this->findRecordByType($record, $type)->libelle_long;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->action === 'edit' && $this->record) {
            $model = $this->findRecordByType($this->record, $this->type);
            $model->update(['libelle_long' => $this->libelle_long]);
        } elseif ($this->action === 'create') {
            $model = $this->getModelByType($this->type);
            $model::create([
                'libelle_long' => $this->libelle_long,
                $this->getForeignKey($this->type) => $this->parent
            ]);
        }

        $this->dispatchBrowserEvent('close-modal');
    }

    private function findRecordByType($id, $type)
    {
        return match ($type) {
            'fonction' => Fonction::find($id),
            'competence' => Competence::find($id),
            'savoirFaire' => SavoirFaire::find($id),
            'activite' => Activite::find($id),
            default => null,
        };
    }

    private function getModelByType($type)
    {
        return match ($type) {
            'fonction' => new Fonction(),
            'competence' => new Competence(),
            'savoirFaire' => new SavoirFaire(),
            'activite' => new Activite(),
            default => null,
        };
    }

    private function getForeignKey($type)
    {
        return match ($type) {
            'fonction' => 'parcours_id',
            'competence' => 'fonction_id',
            'savoirFaire' => 'competence_id',
            'activite' => 'savoirFaire_id',
            default => null,
        };
    }

    
    public function render()
    {
        return view('fcmcentral::livewire.manage-entity-full-parcours-modal',[
            'action'=>$this->action,
            'record'=>$this->record,
            
        ]);
    }
}

