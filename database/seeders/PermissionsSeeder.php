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

        Permission::firstOrCreate([
            "name" => "fcmcentral::voir_les_livrets",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::telecharger_les_livrets",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::suivre_en_fcm",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::change_module_configuration",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::attribuer_note",
            "guard_name" => "web"
        ]);



        //=== PERMISSIONS WIDGETS ===

        // Dashboard User
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_marin",
            "guard_name" => "web"
        ]);

        // Dashboard Marin FCM
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_marin_fcm",
            "guard_name" => "web"
        ]);

        // Dashboard Mentor
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_mentor",
            "guard_name" => "web"
        ]);

         // Dashboard Gestion
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_gestion",
            "guard_name" => "web"
        ]);

         // Dashboard Admin
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_admin",
            "guard_name" => "web"
        ]);

         // Dashboard Supadmin
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget_supadmin",
            "guard_name" => "web"
        ]);




        //=== PERMISSIONS DROITS ===

        // Pour le suivi  (niveau 1)
        Permission::firstOrCreate([
            "name" => "fcmcentral::droits_gestion",
            "guard_name" => "web"
        ]);

        // Pour l'Administration (niveau 2)
        Permission::firstOrCreate([
            "name" => "fcmcentral::droits_admin",
            "guard_name" => "web"
        ]);

        // Tout voir dans FcmCentral  (niveau 1)
        Permission::firstOrCreate([
            "name" => "fcmcentral::droits_supadmin",
            "guard_name" => "web"
        ]);



        //=== PERMISSIONS SPECIFIQUES ===

        // Gerer les cohortes
        Permission::firstOrCreate([
            "name" => "fcmcentral::gerer_cohorte",
            "guard_name" => "web"
        ]);

        // Gerer les marin
        Permission::firstOrCreate([
            "name" => "fcmcentral::gerer_marin",
            "guard_name" => "web"
        ]);

        // Gerer le parcours
        Permission::firstOrCreate([
            "name" => "fcmcentral::gerer_parcours",
            "guard_name" => "web"
        ]);

        // Gerer les notes
        Permission::firstOrCreate([
            "name" => "fcmcentral::gerer_note",
            "guard_name" => "web"
        ]);


       



    }
}