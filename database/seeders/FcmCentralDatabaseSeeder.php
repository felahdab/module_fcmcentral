<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;

class FcmCentralDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class
        ]);
    }
}
