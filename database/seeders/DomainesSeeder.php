<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Domaine;

class DomainesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['uuid' => '323cedcd-08d1-11f1-bc9f-0242c0a88005', 'code' => 'GEN', 'libelle_long' => 'GENOPS', 'libelle_court' => 'GENOPS', 'ordre' => 1, 'color' => 'gray'],
            ['uuid' => '323ceea9-08d1-11f1-bc9f-0242c0a88005', 'code' => 'LSM', 'libelle_long' => 'LSM', 'libelle_court' => 'LSM', 'ordre' => 2, 'color' => 'info'],
            ['uuid' => '323ceede-08d1-11f1-bc9f-0242c0a88005', 'code' => 'SCI', 'libelle_long' => 'SCIENTIFIQUE', 'libelle_court' => 'SCIENTIFIQUE', 'ordre' => 3, 'color' => 'warning'],
            ['uuid' => '323cef05-08d1-11f1-bc9f-0242c0a88005', 'code' => 'ANG', 'libelle_long' => 'ANGLAIS', 'libelle_court' => 'ANGLAIS', 'ordre' => 4, 'color' => 'danger'],
            ['uuid' => '323cef2a-08d1-11f1-bc9f-0242c0a88005', 'code' => 'LOG', 'libelle_long' => 'LOG', 'libelle_court' => 'LOG', 'ordre' => 5, 'color' => 'success'],
            ['uuid' => '323cef4b-08d1-11f1-bc9f-0242c0a88005', 'code' => 'ENC', 'libelle_long' => 'ENCADREMENT', 'libelle_court' => 'ENCADREMENT', 'ordre' => 6, 'color' => 'info'],
            ['uuid' => '323cef6b-08d1-11f1-bc9f-0242c0a88005', 'code' => 'AUT', 'libelle_long' => 'AUTRE', 'libelle_court' => 'AUTRE', 'ordre' => 7, 'color' => 'gray'],
            ['uuid' => '3d6a6935-1e44-11f1-ae69-0242c0a88003', 'code' => 'MCO', 'libelle_long' => 'MCO', 'libelle_court' => 'MCO', 'ordre' => 0, 'color' => 'warning'],
            ['uuid' => '4eb0fcfc-1e44-11f1-ae69-0242c0a88003', 'code' => 'CONDUITE', 'libelle_long' => 'CONDUITE', 'libelle_court' => 'CONDUITE', 'ordre' => 0, 'color' => 'info'],
            ['uuid' => '5cdd90ad-1e44-11f1-ae69-0242c0a88003', 'code' => 'LOG', 'libelle_long' => 'LOG', 'libelle_court' => 'LOG', 'ordre' => 0, 'color' => 'success'],
            ['uuid' => '67e23dab-1e44-11f1-ae69-0242c0a88003', 'code' => 'SECU', 'libelle_long' => 'SECU', 'libelle_court' => 'SECU', 'ordre' => 0, 'color' => 'danger'],
        ];

        foreach ($data as $item) {
            Domaine::firstOrCreate(
                ['uuid' => $item['uuid']],
                $item
            );
        }
    }
}
