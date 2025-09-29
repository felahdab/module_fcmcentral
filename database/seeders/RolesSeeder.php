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

    $role->permissions()->detach();

    $permissions = [
        "fcmcentral::marin.index",
        "fcmcentral::recherche_annuaire",
    ];

    foreach ($permissions as $permName) {
        $permission = Permission::where("name", $permName)->first();
        if ($permission) {
            $role->permissions()->attach($permission->id);
        } else {
            // Optionnel : log ou throw pour savoir ce qui manque
            \Log::warning("Permission manquante : $permName");
        }
    }
}
}