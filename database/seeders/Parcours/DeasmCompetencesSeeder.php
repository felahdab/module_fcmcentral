<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmCompetencesSeeder extends Seeder
{
    
  // Bug Alpine Livewire dans le parcours, j'enleve les caracateres speciaux et accents  pour les seeders
    private  function cleanString($string) {
      return preg_replace('/[^A-Za-z0-9 \-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
  }

    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Classement par PArcours
        $parcoursTypes = DB::table('fcmcentral_parcours_types')->pluck('id','code');
        $affectations = DB::table('fcmcentral_affectations')->pluck('id','code');

        $uuids = [
          'C1' => 'deasm1b1-1234-5678-comp-11aa11z000c1',
          'C2' => 'deasm1b1-1234-5678-comp-11aa11z000c2',
          'C3' => 'deasm1b1-1234-5678-comp-11aa11z000c3',
          'C4' => 'deasm1b1-1234-5678-comp-11aa11z000c4',
          'C5' => 'deasm1b1-1234-5678-comp-11aa11z000c5',
          'C6' => 'deasm1b1-1234-5678-comp-11aa11z000c6',
          'C7' => 'deasm1b1-1234-5678-comp-11aa11z000c7',
          'C8' => 'deasm1b1-1234-5678-comp-11aa11z000c8',
          
        ];

       
      
        $i=1;
        $data = [
          // [
          //   'uuid' => Str::uuid()->toString(),
          //   'code' => 'C0',
          //   'libelle_court' => '/ - Sédimentation - période d\'apprentrissage post BAT',
          //   'libelle_long' => 'Sédimentation - période d\'apprentrissage post BAT',
          //   'ordre' => $i++,
          //   'color' => 'gray',
          //   'parcours_type_id' =>  $parcoursTypes['DEASM'],
          //   'affectation_id' =>  $affectations['A1'],
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
          [
            //'uuid' => Str::uuid()->toString(),
            'uuid' => $uuids['C1'],
            'code' => 'C1',
            'libelle_court' => $this->cleanString("C1 - Identifier son rôle dans la chaîne de commandement et des opérations"),
            'libelle_long' => $this->cleanString("Identifier son rôle dans la chaîne de commandement et des opérations"),
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C2'],
            'code' => 'C2',
            'libelle_court' => $this->cleanString("C2 - Identifier le spectre de la menace selon les vecteurs présents sur la zone"),
            'libelle_long' => $this->cleanString("Identifier le spectre de la menace selon les vecteurs présents sur la zone"),
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C3'],
            'code' => 'C3',
            'libelle_court' => $this->cleanString("C3 - Concevoir une classification technique en fonction de l'environnement et des senseurs ASM"),
            'libelle_long' => $this->cleanString("Concevoir une classification technique en fonction de l'environnement et des senseurs ASM"),
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C4'],
            'code' => 'C4',
            'libelle_court' => $this->cleanString("C4 - Exploiter le tâches ASM suivant le contexte opérationnel"),
            'libelle_long' => $this->cleanString("Exploiter le tâches ASM suivant le contexte opérationnel"),
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C5'],
            'code' => 'C5',
            'libelle_court' => $this->cleanString("C5 - Exploiter les données tactiques en contexte opérationnel interarmées ou interallié"),
            'libelle_long' => $this->cleanString("Exploiter les données tactiques en contexte opérationnel interarmées ou interallié"),
            'ordre' => $i++,
            'color' => 'purple',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C6'],
            'code' => 'C6',
            'libelle_court' => $this->cleanString("C6 - Gérer les munitions pyrotechniques"),
            'libelle_long' => $this->cleanString("Gérer les munitions pyrotechniques"),
            'ordre' => $i++,
            'color' => 'brown',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C7'],
            'code' => 'C7',
            'libelle_court' => $this->cleanString("C7 - Exploiter les armes et leurres A.S.M."),
            'libelle_long' => $this->cleanString("Exploiter les armes et leurres A.S.M."),
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => $uuids['C8'],
            'code' => 'C8',
            'libelle_court' => $this->cleanString("C8 - Réaliser des maintenances préventives et correctives des senseurs et des équipements associés"),
            'libelle_long' => $this->cleanString("Réaliser des maintenances préventives et correctives des senseurs et des équipements associés"),
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          // [
          //   'uuid' => Str::uuid()->toString(),
          //   'code' => 'C9',
          //   'libelle_court' => "C9 - EXA TC",
          //   'libelle_long' => "EXA TC",
          //   'ordre' => $i++,
          //   'color' => 'danger',
          //   'parcours_type_id' =>  $parcoursTypes['DEASM'],
          //   'affectation_id' =>  $affectations['A2'],
          //   'created_at' => now(),
          //   'updated_at' => now(),
          // ],
];
        
        // Utiliser insertOrIgnore pour éviter les doublons
        // collect($data)->chunk(50)->each(function ($chunk) {
        //     DB::table('fcmcentral_fonctions')->insertOrIgnore($chunk->toArray());
        // });

        DB::table('fcmcentral_competences')->insertOrIgnore($data);
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_competences: ' . count($data) . ' enregistrements traités');
    }
}