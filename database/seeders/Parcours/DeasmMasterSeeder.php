<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmMasterSeeder extends Seeder
{
    /**
     * Seeder principal pour le parcours Deasm
     * 
     * 
     * 
     * Hiérarchie: Parcours → Fonctions → Compétences → Savoir-faires → Activités
     * Seeders générés: 9
     */
    public function run()
    {
        
        $this->command->info('🚀 Truncate Table du parcours Deasm...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('fcmcentral_objectifs')->truncate();
        DB::table('fcmcentral_competence_savoirfaires')->truncate();
        DB::table('fcmcentral_affectation_parcours')->truncate();
        DB::table('fcmcentral_parcours')->truncate();
        DB::table('fcmcentral_savoirfaires')->truncate();
        DB::table('fcmcentral_competences')->truncate();
        DB::table('fcmcentral_affectations')->truncate();
        DB::table('fcmcentral_parcours_types')->truncate();
        DB::table('fcmcentral_domaines')->truncate();
        DB::table('fcmcentral_savoirfaire_types')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        
        $this->command->info('🚀 Import du parcours Deasm...');
       

        
        $this->call([

            // Details
            DeasmSavoirfaireTypesSeeder::class,
            DeasmDomainesSeeder::class,
            DeasmParcoursTypesSeeder::class,


            // Catalogue
            DeasmAffectationsSeeder::class,
            DeasmCompetencesSeeder::class,
            DeasmSavoirfairesSeeder::class,
            //DeasmCompetenceSavoirfaireSeeder::class,
            
            // Parcours
            DeasmParcoursSeeder::class,
            
            DeasmAffectationParcoursSeeder::class,
            DeasmCompetenceSavoirfairesSeeder::class,
            DeasmObjectifsSeeder::class,
           
        ]);
        
        $this->command->info('✅ Parcours Deasm importé avec succès !');
    }
}