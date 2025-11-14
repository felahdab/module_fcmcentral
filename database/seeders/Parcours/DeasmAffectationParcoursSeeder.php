<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmAffectationParcoursSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_fonction_parcours
     * Parcours ID: 104
     * Généré le: {\Carbon\Carbon::now()->format('Y-m-d H:i:s')}
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');


        // Recuperer data
        $affectations = DB::table('fcmcentral_affectations')->pluck('id','code');
        $parcours = DB::table('fcmcentral_parcours')->pluck('id','code');
        
        $i=1;
        
        $data = [

            // AN 1
          [
            'parcours_id' => $parcours['DEASM1'],
            'affectation_id' => $affectations['A1'],
            'ordre' => $i++,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'parcours_id' => $parcours['DEASM1'],
            'affectation_id' => $affectations['A2'],
            'ordre' => $i++,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          // [
          //   'parcours_id' => $parcours['DEASM1'],
          //   'annee_id' => $annees['A3'],
          //   'ordre' => $i++,
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
          // [
          //   'parcours_id' => $parcours['DEASM1'],
          //   'annee_id' => $annees['A4'],
          //   'ordre' => $i++,
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
          // [
          //   'parcours_id' => $parcours['DEASM1'],
          //   'annee_id' => $annees['A5'],
          //   'ordre' => $i++,
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
          // [
          //   'parcours_id' => $parcours['DEASM1'],
          //   'annee_id' => $annees['A6'],
          //   'ordre' => $i++,
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
        ];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_affectation_parcours')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_affectation_parcours: ' . count($data) . ' enregistrements traités');
    }
}
