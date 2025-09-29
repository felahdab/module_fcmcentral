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
        $this->command->info('📋 Parcours ID: 104');
        
        $this->call([
            DeasmParcoursSeeder::class,
            DeasmFonctionsSeeder::class,
            DeasmFonctionParcoursSeeder::class,
            DeasmCompetencesSeeder::class,
            DeasmCompetenceFonctionSeeder::class,
            DeasmSavoirfaireSeeder::class,
            DeasmCompetenceSavoirfaireSeeder::class,
            DeasmActivitesSeeder::class,
            DeasmActiviteSavoirfaireSeeder::class,
        ]);
        
        $this->command->info('✅ Parcours Deasm importé avec succès !');
    }
}