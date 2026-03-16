<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;

class DeasmMasterSeeder extends Seeder
{
    /**
     * Import complet du parcours Deasm (idempotent — firstOrCreate).
     *
     *   php artisan module:seed FcmCentral --class=Parcours\\DeasmMasterSeeder
     *
     * Pour repartir de zéro, lancer d'abord :
     * 
     *   php artisan module:seed FcmCentral --class=Parcours\\DeasmTruncateSeeder   
     *   php artisan module:seed FcmCentral --class=Parcours\\DeasmMasterSeeder
     */
    public function run(): void
    {
        $this->command->info('🚀 Import du parcours Deasm...');

        $this->call([
            // Référentiels
            DeasmSavoirfaireTypesSeeder::class,
            DeasmDomainesSeeder::class,
            DeasmParcoursTypesSeeder::class,

            // Catalogue
            DeasmAffectationsSeeder::class,
            DeasmCompetencesSeeder::class,
            DeasmSavoirfairesSeeder::class,

            // Parcours
            DeasmParcoursSeeder::class,
            DeasmAffectationParcoursSeeder::class,
            DeasmCompetenceSavoirfairesSeeder::class,
            DeasmObjectifsSeeder::class,
        ]);

        $this->command->info('✅ Parcours Deasm importé avec succès !');
    }
}
