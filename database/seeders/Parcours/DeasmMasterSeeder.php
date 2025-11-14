<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
 
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