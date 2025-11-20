<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class DeasmCompetencesSeeder extends Seeder
{
    
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Classement par PArcours
        $parcoursTypes = DB::table('fcmcentral_parcours_types')->pluck('id','code');
        $affectations = DB::table('fcmcentral_affectations')->pluck('id','code');
        
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
            'uuid' => Str::uuid()->toString(),
            'code' => 'C1',
            'libelle_court' => "C1 - Identifier son rôle dans la chaîne de commandement et des opérations",
            'libelle_long' => "Identifier son rôle dans la chaîne de commandement et des opérations",
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C2',
            'libelle_court' => "C2 - Identifier le spectre de la menace selon les vecteurs présents sur la zone",
            'libelle_long' => "Identifier le spectre de la menace selon les vecteurs présents sur la zone",
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C3',
            'libelle_court' => "C3 - Concevoir une classification technique en fonction de l'environnement et des senseurs ASM",
            'libelle_long' => "Concevoir une classification technique en fonction de l'environnement et des senseurs ASM",
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C4',
            'libelle_court' => "C4 - Exploiter le tâches ASM suivant le contexte opérationnel",
            'libelle_long' => "Exploiter le tâches ASM suivant le contexte opérationnel",
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C5',
            'libelle_court' => "C5 - Exploiter les données tactiques en contexte opérationnel interarmées ou interallié",
            'libelle_long' => "Exploiter les données tactiques en contexte opérationnel interarmées ou interallié",
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A1'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C6',
            'libelle_court' => "C6 - Gérer les munitions pyrotechniques",
            'libelle_long' => "Gérer les munitions pyrotechniques",
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C7',
            'libelle_court' => "C7 - Exploiter les armes et leurres A.S.M.",
            'libelle_long' => "Exploiter les armes et leurres A.S.M.",
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'affectation_id' =>  $affectations['A2'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C8',
            'libelle_court' => "C8 - Réaliser des maintenances préventives et correctives des senseurs et des équipements associés",
            'libelle_long' => "Réaliser des maintenances préventives et correctives des senseurs et des équipements associés",
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