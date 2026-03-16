<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Domaine;

class DeasmDomainesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => 'GEN', 'libelle_long' => 'GENOPS',       'libelle_court' => 'GENOPS',       'ordre' => 1, 'color' => 'gray'],
            ['code' => 'LSM', 'libelle_long' => 'LSM',          'libelle_court' => 'LSM',          'ordre' => 2, 'color' => 'info'],
            ['code' => 'SCI', 'libelle_long' => 'SCIENTIFIQUE', 'libelle_court' => 'SCIENTIFIQUE', 'ordre' => 3, 'color' => 'warning'],
            ['code' => 'ANG', 'libelle_long' => 'ANGLAIS',      'libelle_court' => 'ANGLAIS',      'ordre' => 4, 'color' => 'danger'],
            ['code' => 'LOG', 'libelle_long' => 'LOG',          'libelle_court' => 'LOG',          'ordre' => 5, 'color' => 'success'],
            ['code' => 'ENC', 'libelle_long' => 'ENCADREMENT',  'libelle_court' => 'ENCADREMENT',  'ordre' => 6, 'color' => 'info'],
            ['code' => 'AUT', 'libelle_long' => 'AUTRE',        'libelle_court' => 'AUTRE',        'ordre' => 7, 'color' => 'gray'],
        ];

        foreach ($data as $item) {
            Domaine::firstOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        $this->command->info('✅ fcmcentral_domaines: ' . count($data) . ' enregistrements traités');
    }
}
