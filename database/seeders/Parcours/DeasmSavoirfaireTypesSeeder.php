<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmSavoirfaireTypesSeeder extends Seeder
{
  
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $data = [
            [ 
                'code'=>'C',
                'libelle_long' => 'Acquis par Compagnonnage',
                'libelle_court' => 'Compagnonnage',
                'ordre' => 1,
                'color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'S',
                'libelle_long' => 'Acquis par Stage',
                'libelle_court' => 'Stage',
                'ordre' => 2,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [ 
                'code'=>'EAD',
                'libelle_long' => 'Acquis par Ecole a Distance',
                'libelle_court' => 'Ecole a Distance',
                'ordre' => 3,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_parcours')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_savoirfaire_types')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_savoirfaire_types: ' . count($data) . ' enregistrements traités');
    }
}