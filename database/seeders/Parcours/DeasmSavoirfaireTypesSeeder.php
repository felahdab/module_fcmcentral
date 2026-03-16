<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\SavoirfaireType;
 
class DeasmSavoirfaireTypesSeeder extends Seeder
{
  
    public function run()
    {
        
        
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
        
       foreach ($data as $item) {
        SavoirfaireType::firstOrCreate(
            ['code' => $item ['code']],
            $item
        );
       }
        
      
        
        $this->command->info('✅ fcmcentral_savoirfaire_types: ' . count($data) . ' enregistrements traités');
    }
}