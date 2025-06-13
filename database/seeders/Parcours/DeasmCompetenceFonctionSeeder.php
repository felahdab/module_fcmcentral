<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmCompetenceFonctionSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_competence_fonction
     * Parcours ID: 104
     * Généré le: {\Carbon\Carbon::now()->format('Y-m-d H:i:s')}
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $data = array (
  0 => 
  array (
    'id' => 81,
    'fonction_id' => 131,
    'competence_id' => 495,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  1 => 
  array (
    'id' => 82,
    'fonction_id' => 131,
    'competence_id' => 497,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  2 => 
  array (
    'id' => 83,
    'fonction_id' => 131,
    'competence_id' => 498,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  3 => 
  array (
    'id' => 84,
    'fonction_id' => 131,
    'competence_id' => 499,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  4 => 
  array (
    'id' => 85,
    'fonction_id' => 131,
    'competence_id' => 503,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_competence_fonction')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_competence_fonction: ' . count($data) . ' enregistrements traités');
    }
}