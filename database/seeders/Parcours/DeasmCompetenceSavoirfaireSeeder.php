<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmCompetenceSavoirfaireSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_competence_savoirfaire
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
    'id' => 108,
    'competence_id' => 495,
    'savoirfaire_id' => 1335,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  1 => 
  array (
    'id' => 109,
    'competence_id' => 495,
    'savoirfaire_id' => 1336,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  2 => 
  array (
    'id' => 110,
    'competence_id' => 495,
    'savoirfaire_id' => 1337,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  3 => 
  array (
    'id' => 111,
    'competence_id' => 495,
    'savoirfaire_id' => 1366,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  4 => 
  array (
    'id' => 112,
    'competence_id' => 495,
    'savoirfaire_id' => 1368,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  5 => 
  array (
    'id' => 113,
    'competence_id' => 497,
    'savoirfaire_id' => 1342,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  6 => 
  array (
    'id' => 114,
    'competence_id' => 498,
    'savoirfaire_id' => 1346,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  7 => 
  array (
    'id' => 115,
    'competence_id' => 498,
    'savoirfaire_id' => 1339,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  8 => 
  array (
    'id' => 116,
    'competence_id' => 498,
    'savoirfaire_id' => 1341,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  9 => 
  array (
    'id' => 117,
    'competence_id' => 498,
    'savoirfaire_id' => 1340,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  10 => 
  array (
    'id' => 118,
    'competence_id' => 498,
    'savoirfaire_id' => 1345,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  11 => 
  array (
    'id' => 119,
    'competence_id' => 498,
    'savoirfaire_id' => 1347,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  12 => 
  array (
    'id' => 120,
    'competence_id' => 498,
    'savoirfaire_id' => 1374,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  13 => 
  array (
    'id' => 121,
    'competence_id' => 498,
    'savoirfaire_id' => 1373,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  14 => 
  array (
    'id' => 122,
    'competence_id' => 498,
    'savoirfaire_id' => 1371,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  15 => 
  array (
    'id' => 123,
    'competence_id' => 499,
    'savoirfaire_id' => 1343,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  16 => 
  array (
    'id' => 124,
    'competence_id' => 499,
    'savoirfaire_id' => 1344,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  17 => 
  array (
    'id' => 125,
    'competence_id' => 503,
    'savoirfaire_id' => 1365,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_competence_savoirfaire')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_competence_savoirfaire: ' . count($data) . ' enregistrements traités');
    }
}