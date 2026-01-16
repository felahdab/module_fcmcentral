<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            "name" => "fcmcentral::recherche_annuaire",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::voir_tous_les_marins",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::change_configuration_module",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer.parcours",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer.mentor",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer.cohorte",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::definir_jalons_de_fcm",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::marin.suivreEnFcm",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::parcours.figer",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::exporter_csv",
            "guard_name" => "web"
        ]);
        

        //=== LIVRET ===

        Permission::firstOrCreate([
            "name" => "fcmcentral::livret.voir",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::livret.proposer",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::livret.valider",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::livret.annuler",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::livret.telecharger",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::ficheBilan.voir",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::note.valider",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::note.creer",
            "guard_name" => "web"
        ]);

    }
}