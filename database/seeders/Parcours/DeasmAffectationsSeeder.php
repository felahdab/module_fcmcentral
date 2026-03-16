<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Affectation;

class DeasmAffectationsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'uuid'          => 'deasm1b1-1234-5678-affe-11aa11z00a01',
                'code'          => 'A1',
                'libelle_court' => 'AFFECTATION 1',
                'libelle_long'  => 'Affectation 1',
                'ordre'         => 1,
                'color'         => 'gray',
                'nombre'        => 1,
            ],
            [
                'uuid'          => 'deasm1b1-1234-5678-affe-11aa11z00a02',
                'code'          => 'A2',
                'libelle_court' => 'AFFECTATION 2',
                'libelle_long'  => 'Affectation 2',
                'ordre'         => 2,
                'color'         => 'info',
                'nombre'        => 2,
            ],
        ];

        foreach ($data as $item) {
            Affectation::firstOrCreate(
                ['uuid' => $item['uuid']],
                $item
            );
        }

        $this->command->info('✅ fcmcentral_affectations: ' . count($data) . ' enregistrements traités');
    }
}
