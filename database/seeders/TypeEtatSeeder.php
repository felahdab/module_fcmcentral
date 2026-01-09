<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\TypeEtat;

class TypeEtatSeeder extends Seeder
{
    public function run(): void
    {
        TypeEtat::updateOrCreate(['code' => 'GEL_PAU'], [
            'libelle_court' => 'En Pause',
        ]);
        
    }
}
