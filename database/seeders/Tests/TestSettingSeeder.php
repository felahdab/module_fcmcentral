<?php

namespace Modules\FcmCentral\Database\Seeders\Tests;

use Illuminate\Database\Seeder;

use App\Models\Setting;

class TestSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create(["key" => "fcmcentral"]);
    }
}
