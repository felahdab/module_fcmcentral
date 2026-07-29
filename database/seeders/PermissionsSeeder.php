<?php

namespace Modules\FcmCentral\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Permission::firstOrCreate([
            'name' => 'fcmcentral::recherche_annuaire',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::voir_tous_les_marins',
            'guard_name' => 'web',
        ]);


        Permission::firstOrCreate([
            'name' => 'fcmcentral::change_configuration_module',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::attribuer.parcours',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::attribuer.mentor',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::attribuer.cohorte',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::definir_jalons_de_fcm',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::marin.suivreEnFcm',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::parcours.figer',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::exporter_csv',
            'guard_name' => 'web',
        ]);

        // === LIVRET ===

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.voir',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.proposer',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.valider',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.annuler',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.refuser',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.telecharger',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::ficheBilan.voir',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::livret.telecharger',
            'guard_name' => 'web',
        ]);


        Permission::firstOrCreate([
            'name' => 'fcmcentral::note.valider',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::note.creer',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::note.voir',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::fcm.admin',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::piece_jointe.voir',
            'guard_name' => 'web',
        ]);



        // Journal des évènements
        Permission::firstOrCreate([
            'name' => 'fcmcentral::voir_journal_des_evenements',
            'guard_name' => 'web',
        ]);

        // Pages de suivi / statistiques
        Permission::firstOrCreate([
            'name' => 'fcmcentral::suivi.stage',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::suivi.ead',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::suivi.savoirfaire',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::suivi.competence',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'fcmcentral::suivi.objectif',
            'guard_name' => 'web',
        ]);
        
    }
}
