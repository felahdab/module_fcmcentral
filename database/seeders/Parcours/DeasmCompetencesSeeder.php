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
        
        $i=1;
        $data = [
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C0',
            'libelle_court' => '/ - Sédimentation - période d\'apprentrissage post BAT',
            'libelle_long' => 'Sédimentation - période d\'apprentrissage post BAT',
            'ordre' => $i++,
            'color' => 'gray',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C1',
            'libelle_court' => 'C1 - Caractériser le spectre de la menace selon les vecteurs présents sur le théâtre d’opérations',
            'libelle_long' => 'Caractériser le spectre de la menace selon les vecteurs présents sur le théâtre d’opérations',
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C4',
            'libelle_court' => 'C4 - Classifier les vecteurs aéromaritimes du domaine de lutte L.A.S. selon l’évolution de la SITAC OPS',
            'libelle_long' => 'Classifier les vecteurs aéromaritimes du domaine de lutte L.A.S. selon l’évolution de la SITAC OPS',
            'ordre' => $i++,
            'color' => 'warning',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C6',
            'libelle_court' => 'C6 - Elaborer une classification technique en fonction de l’environnement et en optimisant l’utilisation des senseurs A.S.M. disponibles',
            'libelle_long' => 'Elaborer une classification technique en fonction de l’environnement et en optimisant l’utilisation des senseurs A.S.M. disponibles',
            'ordre' => $i++,
            'color' => 'danger',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C9',
            'libelle_court' => 'C9 - Partager les données tactiques avec les unités participantes en contexte opérationnel interarmées ou interallié',
            'libelle_long' => 'Partager les données tactiques avec les unités participantes en contexte opérationnel interarmées ou interallié',
            'ordre' => $i++,
            'color' => 'success',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C12',
            'libelle_court' => 'C12 - Assurer la gestion administrative des munitions pyrotechniques en fonction des besoins de la mission opérationnelle',
            'libelle_long' => 'Assurer la gestion administrative des munitions pyrotechniques en fonction des besoins de la mission opérationnelle',
            'ordre' => $i++,
            'color' => 'gray',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C13',
            'libelle_court' => 'C13 - Superviser la mise en œuvre des armes et leurres A.S.M.',
            'libelle_long' => 'Superviser la mise en œuvre des armes et leurres A.S.M.',
            'ordre' => $i++,
            'color' => 'info',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C15',
            'libelle_court' => 'C15 - Réaliser des maintenances PREV et CORR des senseurs OPS et des équipements associés en environnement automatisé et informatisé',
            'libelle_long' => 'Réaliser des maintenances PREV et CORR des senseurs OPS et des équipements associés en environnement automatisé et informatisé',
            'ordre' => $i++,
            'color' => 'warning',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'code' => 'C19',
            'libelle_court' => 'C19 - EXA TC',
            'libelle_long' => 'EXA TC',
            'ordre' => $i++,
            'color' => 'danger',
            'parcours_type_id' =>  $parcoursTypes['DEASM'],
            'created_at' => now(),
            'updated_at' => now(),
          ],
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