<?php
 
 namespace Modules\FcmCentral\Database\Seeders\Parcours;

 use Illuminate\Database\Seeder;
 use Illuminate\Support\Facades\DB;
 
 class DeasmCompetenceFonctionSeeder extends Seeder
 {
     public function run()
     {
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 
         // Récupérer tous les IDs de fonctions
         $fonctionIds = DB::table('fcmcentral_fonctions')->pluck('id')->toArray();
 
         // Récupérer tous les IDs de compétences
         $competenceIds = DB::table('fcmcentral_competences')->pluck('id')->toArray();
 
         $data = [];
         foreach ($fonctionIds as $fonctionId) {
          $ordre = 1 ;
             foreach ($competenceIds as $competenceId) {
                 $data[] = [
                     'fonction_id' => $fonctionId,
                     'competence_id' => $competenceId,
                     'ordre' => $ordre,
                     'same' => null,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ];
                 $ordre++;
             }
         }
 
         if (count($data)) {
             DB::table('fcmcentral_competence_fonction')->insertOrIgnore($data);
             $this->command->info('✅ fcmcentral_competence_fonction: ' . count($data) . ' enregistrements traités');
         } else {
             $this->command->warn('❌ Aucune liaison insérée');
         }
 
         DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     }
 }
 
 