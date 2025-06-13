<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmCompetencesSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_competences
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
    'id' => 495,
    'uuid' => 'df4fdb01-94e9-4d71-949d-79beedad4bb6',
    'libelle_long' => 'C1 - Caractériser le spectre de la menace selon les vecteurs présents sur le théâtre d\'opérations',
    'libelle_court' => 'C1 - Caractériser le spectre de la menace',
    'url' => NULL,
    'same' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  1 => 
  array (
    'id' => 497,
    'uuid' => '19a7e7f5-992a-4138-b912-24a2c63a138f',
    'libelle_long' => 'C9 - Partager les données tactiques avec les unités participantes en contexte opérationnel interarmées ou interallié',
    'libelle_court' => 'C9 - Partager les données tactiques',
    'url' => NULL,
    'same' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  2 => 
  array (
    'id' => 498,
    'uuid' => '93a83156-e606-45a4-ba00-5e34e0703547',
    'libelle_long' => 'C6 - Elaborer une classification technique en fonction de l’environnement et en optimisant l’utilisation des senseurs A.S.M. disponibles',
    'libelle_court' => 'C6 - Elaborer une classification technique',
    'url' => NULL,
    'same' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  3 => 
  array (
    'id' => 499,
    'uuid' => '06df8696-861f-4f12-b9de-f202878ff6b5',
    'libelle_long' => 'C13 - Superviser la mise en œuvre des armes et leurres A.S.M.',
    'libelle_court' => 'C13 - Superviser la mise en œuvre des armes et leurres A.S.M.',
    'url' => NULL,
    'same' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  4 => 
  array (
    'id' => 503,
    'uuid' => 'a5bf734c-d3a9-47cc-8f3e-d60beb3d2e64',
    'libelle_long' => 'C4 - Appliquer les concepts de lutte dans les domaines de lutte au-dessus de la surface',
    'libelle_court' => 'C4 - Appliquer les concepts de lutte dans les domaines de lutte au-dessus de la surface',
    'url' => NULL,
    'same' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_competences')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_competences: ' . count($data) . ' enregistrements traités');
    }
}