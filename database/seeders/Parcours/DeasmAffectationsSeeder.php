<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmAffectationsSeeder extends Seeder
{
    
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $uuids = [
          'A1' => 'deasm1b1-1234-5678-affe-11aa11z00a01',
          'A2' => 'deasm1b1-1234-5678-affe-11aa11z00a02',
          
        ];
          
      
        
        $data = [
          [
            //'uuid' => Str::uuid()->toString(),
            'uuid' => $uuids['A1'],
            'code' => 'A1',
            'libelle_court' => 'AFFECTATION 1',
            'libelle_long' => 'Affectation 1',
            'ordre' => 1,
            'color' => 'gray',
            'nombre' => 1,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['A2'],
            'code' => 'A2',
            'libelle_court' => 'AFFECTATION 2',
            'libelle_long' => 'Affectation 2',
            'ordre' => 2,
            'color' => 'info',
            'nombre' => 2,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          
          
];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_fonctions')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_affectations')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_affectations: ' . count($data) . ' enregistrements traités');
    }
}