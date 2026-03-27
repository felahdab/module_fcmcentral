<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\TypeEtat;

class TypeEtatSeeder extends Seeder
{
    public function run(): void
    {
        TypeEtat::firstOrCreate(['uuid' => '323ac8f1-08d1-11f1-bc9f-0242c0a88005'], [
            'code' => 'GEL_PAU',
            'libelle_court' => 'En Pause',
        ]);
    }
}
