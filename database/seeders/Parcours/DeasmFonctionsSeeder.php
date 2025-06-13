<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmFonctionsSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_fonctions
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
    'id' => 131,
    'uuid' => '68df5108-0e51-49ce-9636-11ef5a6b4866',
    'libelle_long' => 'Compagnonnage N1 N2 N3 : CASM/CPC - ADJOINT BUROPS',
    'libelle_court' => 'Comp CASM/CPC - ADJOINT BUROPS',
    'url' => NULL,
    'ordre' => 0,
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_fonctions')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_fonctions: ' . count($data) . ' enregistrements traités');
    }
}