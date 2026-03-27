<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Models\Parcours;

class ViewParcours extends ViewRecord
{
    public $parcours;
    protected static string $resource = ParcoursResource::class;
    protected string $view = 'fcmcentral::filament.fcmcentral.resources.parcours-resource.pages.view-parcours';

    public function mount($record): void
    {
        parent::mount($record);
        $this->parcours = $this->getFullParcours($record);
    }

    public function getFullParcours(int $id): ?Parcours
    {
        // return Parcours::with([
        //     'affectations.competences.competenceSavoirfaires.objectifs',
        //     'parcoursType'
        // ])->find($id);

        $parcours = Parcours::find($id);
        if (!$parcours) {
            return null;
        }

        return Parcours::with([
            'affectations' => function ($query) {
                $query->orderBy('fcmcentral_affectation_parcours.ordre');
            },
            'affectations.competences' => function ($query) use ($parcours) {
                $query->where('parcours_type_id', $parcours->parcours_type_id)
                    ->orderBy('ordre')
                ;
            },
            'affectations.competences.competenceSavoirfaires' => function ($query) {
                $query->orderBy('ordre'); // ✅
            },
            'affectations.competences.competenceSavoirfaires.savoirfaire',
            'affectations.competences.competenceSavoirfaires.savoirfaireType',
            'affectations.competences.competenceSavoirfaires.objectifs' => function ($query) {
                $query->orderBy('ordre'); // ✅
            },
            'parcoursType',
        ])->find($id);
    }
}
