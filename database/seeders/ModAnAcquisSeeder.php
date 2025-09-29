<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModAnAcquisSeeder extends Seeder
{
    public function run()
    {
        // Modes d'acquisition
        DB::table('fcmcentral_mod_acquis')->insertOrIgnore([
            ['code' => 'C', 'libelle_court' => 'Compagnonnage', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'S', 'libelle_court' => 'Stage', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EAD', 'libelle_court' => 'Ecole A Distance', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Années d'acquisition
        DB::table('fcmcentral_an_acquis')->insertOrIgnore([
            ['code' => '1', 'libelle_court' => 'Année 1', 'libelle_long' => 'Sédimentation - période d\'apprentissage post BAT', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '2', 'libelle_court' => 'Année 2', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '3', 'libelle_court' => 'Année 3', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '4', 'libelle_court' => 'Année 4', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '5', 'libelle_court' => 'Année 5', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '6', 'libelle_court' => 'Année 6', 'libelle_long' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
