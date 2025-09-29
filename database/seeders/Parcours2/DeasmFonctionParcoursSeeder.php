<?php
 
 namespace Modules\FcmCentral\Database\Seeders\Parcours;

 use Illuminate\Database\Seeder;
 use Illuminate\Support\Facades\DB;
 
 class DeasmFonctionParcoursSeeder extends Seeder
 {
     public function run()
     {
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 
         // Récupérer les IDs dynamiquement via les libellés courts
         $parcoursId = DB::table('fcmcentral_parcours')
             ->where('libelle_court', 'DEASM')
             ->value('id');
 
        //  $fonctionIds = DB::table('fcmcentral_fonctions')
        //      ->whereIn('libelle_court', ['FCT1', 'FCT2'])
        //      ->pluck('id')
        //      ->toArray();

          $fonctionIds = DB::table('fcmcentral_fonctions')->pluck('id')->toArray();

         $data = [];
         $ordre = 1 ;
         foreach ($fonctionIds as $fonctionId) {
             $data[] = [
                 'parcours_id' => $parcoursId,
                 'fonction_id' => $fonctionId,
                 'ordre' => $ordre,
                 'created_at' => now(),
                 'updated_at' => now(),
             ];
         }
 
         if ($parcoursId && count($data)) {
             DB::table('fcmcentral_fonction_parcours')->insertOrIgnore($data);
             $this->command->info('✅ fcmcentral_fonction_parcours: ' . count($data) . ' enregistrements traités');
         } else {
             $this->command->warn('❌ parcours_id ou fonction_id introuvable, rien inséré');
         }
 
         DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     }
 }
 