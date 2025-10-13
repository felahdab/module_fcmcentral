<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmAnneesSeeder extends Seeder
{
    
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

       
        
        $data = [
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A1',
            'libelle_court' => 'ANNEE 1',
            'libelle_long' => 'Année 1',
            'ordre' => 1,
            'color' => 'gray',
            'nombre' => 1,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A2',
            'libelle_court' => 'ANNEE 2',
            'libelle_long' => 'Année 2',
            'ordre' => 2,
            'color' => 'info',
            'nombre' => 2,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A3',
            'libelle_court' => 'ANNEE 3',
            'libelle_long' => 'Année 3',
            'ordre' => 3,
            'color' => 'warning',
            'nombre' => 3,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A4',
            'libelle_court' => 'ANNEE 4',
            'libelle_long' => 'Année 4',
            'ordre' => 4,
            'color' => 'danger',
            'nombre' => 4,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A5',
            'libelle_court' => 'ANNEE 5',
            'libelle_long' => 'Année 5',
            'ordre' => 5,
            'color' => 'success',
            'nombre' => 5,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'A6',
            'libelle_court' => 'ANNEE 6',
            'libelle_long' => 'Année 6',
            'ordre' => 6,
            'color' => 'danger',
            'nombre' => 6,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          
];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_fonctions')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_annees')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_annees: ' . count($data) . ' enregistrements traités');
    }
}