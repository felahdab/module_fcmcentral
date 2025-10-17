<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Annee;
use Modules\FcmCentral\Models\AnneeSavoirfaire;
use Modules\FcmCentral\Models\Objectif;
use Filament\Notifications\Notification;

class ViewParcours extends ViewRecord
{
    protected static string $resource = ParcoursResource::class;
    protected static string $view = 'fcmcentral::filament.fcmcentral.resources.parcours-resource.pages.view-parcours';

    public $parcours;
    public $annee_id;
    public $savoirfaire_id = [];
    public $pivot_libelle_court = [];
    public $pivot_heures = [];
    public $pivot_jours = [];
    public $pivot_ordre = [];
    public $pivot_savoirfaire_type_id = [];
    public $editingAsfId = null;
    public $editingAsf = [
        'libelle_court' => '',
        'heures' => null,
        'jours' => null,
        'ordre' => null,
    ];

    public function mount($record): void
    {
        parent::mount($record);
        $this->parcours = $this->getFullParcours($record);
    }

    public function getFullParcours(int $id): ?Parcours
    {
        return Parcours::with([
            'annees.anneeSavoirfaires.objectifs',
            'parcoursType'
        ])->find($id);
    }

    public function addAnnee()
    {
        if (!$this->annee_id) {
            Notification::make()->title('Sélection requise')->danger()->send();
            return;
        }
        if ($this->parcours->annees->contains($this->annee_id)) {
            Notification::make()->title('Année déjà liée')->warning()->send();
            return;
        }
        $this->parcours->annees()->attach($this->annee_id);
        Notification::make()->title('Année ajoutée')->success()->send();
        $this->parcours = $this->getFullParcours($this->parcours->id);
        $this->annee_id = null;
    }

    public function removeAnnee($anneeId)
    {
        $this->parcours->annees()->detach($anneeId);
        Notification::make()->title('Année détachée')->success()->send();
        $this->parcours = $this->getFullParcours($this->parcours->id);
    }

    public function addSavoirFaire($anneeId)
    {
        $sfId = $this->savoirfaire_id[$anneeId] ?? null;
        $pivot = [
            'libelle_court' => $this->pivot_libelle_court[$anneeId] ?? null,
            'heures' => $this->pivot_heures[$anneeId] ?? null,
            'jours' => $this->pivot_jours[$anneeId] ?? null,
            'ordre' => $this->pivot_ordre[$anneeId] ?? null,
            'savoirfaire_type_id' => $this->pivot_savoirfaire_type_id[$anneeId] ?? null,
        ];
        if (!$sfId) {
            Notification::make()->title('Sélection requise')->danger()->send();
            return;
        }
        $asf = AnneeSavoirfaire::create(array_merge([
            'annee_id' => $anneeId,
            'savoirfaire_id' => $sfId,
        ], $pivot));
        if ($this->parcours->parcoursType && $this->parcours->parcoursType->code === 'C') {
            Objectif::create([
                'annee_savoirfaire_id' => $asf->id,
                'libelle_court' => 'Objectif automatique',
            ]);
        }
        Notification::make()->title('Savoir-faire ajouté')->success()->send();
        $this->parcours = $this->getFullParcours($this->parcours->id);
        $this->savoirfaire_id[$anneeId] = null;
        $this->pivot_libelle_court[$anneeId] = null;
        $this->pivot_heures[$anneeId] = null;
        $this->pivot_jours[$anneeId] = null;
        $this->pivot_ordre[$anneeId] = null;
        $this->pivot_savoirfaire_type_id[$anneeId] = null;
    }

    public function removeSavoirFaireFromAnnee($asfId)
    {
        $asf = AnneeSavoirfaire::find($asfId);
        if ($asf) {
            $asf->objectifs()->delete();
            $asf->delete();
            Notification::make()->title('Savoir-faire supprimé')->success()->send();
            $this->parcours = $this->getFullParcours($this->parcours->id);
            if ($this->editingAsfId === $asfId) {
                $this->editingAsfId = null;
                $this->editingAsf = ['libelle_court' => '', 'heures' => null, 'jours' => null, 'ordre' => null];
            }
        }
    }

    public function startEditAsf($asfId)
    {
        // dd('test');
        $asf = AnneeSavoirfaire::find($asfId);
        if ($asf) {
            $this->editingAsfId = $asfId;
            $this->editingAsf = [
                'libelle_court' => $asf->libelle_court,
                'heures' => $asf->heures,
                'jours' => $asf->jours,
                'ordre' => $asf->ordre,
            ];
        }
    }

    public function saveEditAsf()
    {
        if (!$this->editingAsfId) return;
        $asf = AnneeSavoirfaire::find($this->editingAsfId);
        if ($asf) {
            $asf->update($this->editingAsf);
            Notification::make()->title('Mise à jour OK')->success()->send();
            $this->parcours = $this->getFullParcours($this->parcours->id);
            $this->editingAsfId = null;
            $this->editingAsf = ['libelle_court' => '', 'heures' => null, 'jours' => null, 'ordre' => null];
        }
    }

    public function cancelEditAsf()
    {
        $this->editingAsfId = null;
        $this->editingAsf = ['libelle_court' => '', 'heures' => null, 'jours' => null, 'ordre' => null];
    }
}

