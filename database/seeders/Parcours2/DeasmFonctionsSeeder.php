<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmFonctionsSeeder extends Seeder
{
    
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $data = [
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'F1',
            'libelle_court' => 'BUROPS - CASM/CPC',
            'libelle_long' => 'Adjoint BUROPS Comp CASM/CPC',
            'url' => NULL,
            'ordre' => 1,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          
];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_fonctions')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_fonctions')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_fonctions: ' . count($data) . ' enregistrements traités');
    }
}