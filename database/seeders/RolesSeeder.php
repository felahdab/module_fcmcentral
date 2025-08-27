<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::firstOrCreate([
            "name" => "fcmcentral::superviseur",
            "guard_name" => "web"
        ]);

        $permission = Permission::where("name", "fcmcentral::marin.index")->first();

        $role->permissions()->attach([
            $permission
        ]);

    }
}