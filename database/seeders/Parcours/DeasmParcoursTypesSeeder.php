<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\ParcoursType;
 
class DeasmParcoursTypesSeeder extends Seeder
{
  
    public function run()
    {
       
        
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
        
       foreach ($data as $item){
        ParcoursType::firstOrCreate(
            ['code' => $item['code']],
            $item
        );
       }
        
        $this->command->info('✅ fcmcentral_parcours_types: ' . count($data) . ' enregistrements traités');
    }
}