<?php

namespace Modules\FcmCentral\Database\Seeders;

use Database\Seeders\TypeEtatSeeder;
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
            ///ModAnAcquisSeeder::class,
            //FcmSeeder::class,
            DeasmMasterSeeder::class,
            PermissionsSeeder::class,
            RolesSeeder::class,
            //TestMarinsRolesSeeder::class, # ce seeder peut être utilisé par le développeur en ligne de commande.
            TypeEtatSeeder::class
        ]);
    }
}
