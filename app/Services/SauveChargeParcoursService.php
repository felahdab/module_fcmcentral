<?php

namespace Modules\FcmCentral\Services;

use Illuminate\Support\Facades\File;
use Modules\FcmCentral\Listeners\Save\CreationParcoursSerialiseListener;
use Modules\FcmCentral\Models\Parcours;

class SauveChargeParcoursService
{
    public function __construct(public CreationParcoursSerialiseListener $listener) {}

    public function sauvegarde(Parcours $parcours, string $filename)
    {
        // Prends le parcours et tout ce qui est nécessaire à sa définition, et en fait un objet que l'on peut sérialiser
        $parcours->load([
            'affectations' => function ($q) {
                $q->orderBy('fcmcentral_affectation_parcours.ordre');
            },
            'affectations.competences' => function ($q) {
                $q->orderBy('ordre');
            },
            'affectations.competences.competenceSavoirfaires' => function ($q) {
                $q->orderBy('ordre');
            },
            'affectations.competences.competenceSavoirfaires.savoirfaire',
            'affectations.competences.competenceSavoirfaires.savoirfaireType',
            'affectations.competences.competenceSavoirfaires.objectifs', // si objectifs relation existe
        ]);

        // Construire la structure sérialisée
        $serializedData = $this->listener->buildCompleteParcoursData($parcours);

        $fileContent = json_encode($serializedData, JSON_PRETTY_PRINT);

        File::put($filename, $fileContent);
    }

    public function charge_from_file(string $filename) {}

    public function charge_from_content(string $content) {}
}
