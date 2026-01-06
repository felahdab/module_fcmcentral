<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmDomainesSeeder extends Seeder
{
  
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $i=1;
        $data = [
            
            [ 
                'code' =>'GEN',
                'libelle_long' => 'GENOPS',
                'libelle_court' => 'GENOPS',
                'ordre' => $i++,
                'color' => 'gray',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'LSM',
                'libelle_long' => 'LSM',
                'libelle_court' => 'LSM',
                'ordre' => $i++,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'SCI',
                'libelle_long' => 'SCIENTIFIQUE',
                'libelle_court' => 'SCIENTIFIQUE',
                'ordre' => $i++,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'ANG',
                'libelle_long' => 'ANGLAIS',
                'libelle_court' => 'ANGLAIS',
                'ordre' => $i++,
                'color' => 'danger',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'LOG',
                'libelle_long' => 'LOG',
                'libelle_court' => 'LOG',
                'ordre' => $i++,
                'color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'ENC',
                'libelle_long' => 'ENCADREMENT',
                'libelle_court' => 'ENCADREMENT',
                'ordre' => $i++,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'AUT',
                'libelle_long' => 'AUTRE',
                'libelle_court' => 'AUTRE',
                'ordre' => $i++,
                'color' => 'gray',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_parcours')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_domaines')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_domaines: ' . count($data) . ' enregistrements traités');
    }
}