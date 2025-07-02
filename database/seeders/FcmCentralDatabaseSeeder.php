<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Database\Seeders\Parcours\DeasmMasterSeeder;

class FcmCentralDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            FcmSeeder::class,
            DeasmMasterSeeder::class,
            
        ]);
    }
}
