<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer_cohorte",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer_mentor",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer_parcours",
            "guard_name" => "web"
        ]);
        
        Permission::firstOrCreate([
            "name" => "fcmcentral::valider_element_parcours",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::voir_fiche_bilan",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::recherche_annuaire",
            "guard_name" => "web"
        ]);

    }
}