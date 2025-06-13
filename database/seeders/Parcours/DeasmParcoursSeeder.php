<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmParcoursSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_parcours
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
    'id' => 104,
    'uuid' => '5eb56381-ee60-4442-ac70-736c102a146d',
    'libelle_long' => 'DEASM (DDC NIVEAU 1, 2 et 3) - (ETR 101856)',
    'libelle_court' => 'DEASM (DDC NIVEAU 1, 2 et 3)',
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_parcours')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_parcours: ' . count($data) . ' enregistrements traités');
    }
}