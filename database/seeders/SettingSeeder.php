<?php

namespace Modules\FcmCentral\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Setting::firstOrCreate(['key' => 'fcmcentral']);
    }
}
