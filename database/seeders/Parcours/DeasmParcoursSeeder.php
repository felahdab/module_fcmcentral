<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmParcoursSeeder extends Seeder
{
  
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $parcourstype = DB::table('fcmcentral_parcours_types')->pluck('id','code');
        
        $data = [
            [ 
              'uuid' => Str::uuid()->toString(),
              'code'=>'DEASM1',
              'libelle_long' => 'DEASM (DDC NIVEAU 1, 2 et 3) - (ETR 101856)',
              'libelle_court' => 'DEASM',
              'ordre' => 1,
              'parcours_type_id' => $parcourstype['DEASM'],
              'created_at' => now(),
              'updated_at' => now(),
            ],
        ];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_parcours')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_parcours')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_parcours: ' . count($data) . ' enregistrements traités');
    }
}