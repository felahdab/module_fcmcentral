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
        //\App\Models\Permission::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
    
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
        
       

        Permission::firstOrCreate([
            "name" => "fcmcentral::recherche_annuaire",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::change_module_configuration",
            "guard_name" => "web"
        ]);

        
        //=== ATTRIBUER ===

        

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
            "name" => "fcmcentral::marin.suivreEnFcm",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::parcoursSerialise.figer",
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
            "name" => "fcmcentral::note.voir",
            "guard_name" => "web"
        ]);

        


        //=== PERMISSIONS WIDGETS ===

        // Dashboard Marin
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget.marin",
            "guard_name" => "web"
        ]);

        // Dashboard Mentor
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget.mentor",
            "guard_name" => "web"
        ]);

        // Dashboard ChefdeService
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget.chefdeservice",
            "guard_name" => "web"
        ]);

         // Dashboard CDT
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget.cdt",
            "guard_name" => "web"
        ]);

         // Dashboard FPS
        Permission::firstOrCreate([
            "name" => "fcmcentral::widget.fps",
            "guard_name" => "web"
        ]);

         // Dashboard BCF
         Permission::firstOrCreate([
            "name" => "fcmcentral::widget.bcf",
            "guard_name" => "web"
        ]);


        //=== PERMISSIONS SPECIAL ===
        // Pour des pages speciales ou autres ( ex Resource Documentations )

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.chefdeservice",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.mentor",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.marin",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.bcf",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.fps",
            "guard_name" => "web"
        ]);

        Permission::firstOrCreate([
            "name" => "fcmcentral::permission.cdt",
            "guard_name" => "web"
        ]);

       




       


       



    }
}