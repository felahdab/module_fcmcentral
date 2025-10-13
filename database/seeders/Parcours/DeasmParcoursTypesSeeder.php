<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmParcoursTypesSeeder extends Seeder
{
  
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $data = [
            [ 
                'code' =>'DEASM',
                'libelle_long' => 'DEASM',
                'libelle_court' => 'DEASM',
                'ordre' => 1,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'ELECT',
                'libelle_long' => 'ELECT',
                'libelle_court' => 'ELECT',
                'ordre' => 2,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_parcours')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_parcours_types')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_parcours_types: ' . count($data) . ' enregistrements traités');
    }
}