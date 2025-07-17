<?php

namespace Modules\FcmCentral\Database\Seeders\Tests;

use Illuminate\Database\Seeder;


class TestParcoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TestSettingSeeder::class,
            TestFcmSeeder::class,
           
            
        ]);
    }
}
