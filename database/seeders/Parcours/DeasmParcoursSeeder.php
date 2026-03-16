<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursType;

class DeasmParcoursSeeder extends Seeder
{
    public function run(): void
    {
        $parcoursType = ParcoursType::where('code', 'DEASM')->firstOrFail();

        Parcours::firstOrCreate(
            ['uuid' => 'deasm1b1-1234-5678-parc-11aa11z000p1'],
            [
                'code'             => 'DEASM1',
                'libelle_long'     => 'DEASM',
                'libelle_court'    => 'DEASM',
                'ordre'            => 1,
                'parcours_type_id' => $parcoursType->id,
            ]
        );

        $this->command->info('✅ fcmcentral_parcours: 1 enregistrement traité');
    }
}
