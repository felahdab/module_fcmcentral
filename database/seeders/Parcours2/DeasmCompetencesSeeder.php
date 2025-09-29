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
        
        $data = [
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C1',
            'libelle_court' => 'C1 - Caractériser le spectre de la menace selon les vecteurs présents sur le théâtre d’opérations',
            'libelle_long' => 'Caractériser le spectre de la menace selon les vecteurs présents sur le théâtre d’opérations',
            'url' => NULL,
            'ordre' => 1,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C4',
            'libelle_court' => 'C4 - Classifier les vecteurs aéromaritimes du domaine de lutte L.A.S. selon l’évolution de la SITAC OPS',
            'libelle_long' => 'Classifier les vecteurs aéromaritimes du domaine de lutte L.A.S. selon l’évolution de la SITAC OPS',
            'url' => NULL,
            'ordre' => 2,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C6',
            'libelle_court' => 'C6 - Elaborer une classification technique en fonction de l’environnement et en optimisant l’utilisation des senseurs A.S.M. disponibles',
            'libelle_long' => 'Elaborer une classification technique en fonction de l’environnement et en optimisant l’utilisation des senseurs A.S.M. disponibles',
            'url' => NULL,
            'ordre' => 3,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C9',
            'libelle_court' => 'C9 - Partager les données tactiques avec les unités participantes en contexte opérationnel interarmées ou interallié',
            'libelle_long' => 'Partager les données tactiques avec les unités participantes en contexte opérationnel interarmées ou interallié',
            'url' => NULL,
            'ordre' => 4,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C12',
            'libelle_court' => 'C12 - Assurer la gestion administrative des munitions pyrotechniques en fonction des besoins de la mission opérationnelle',
            'libelle_long' => 'Assurer la gestion administrative des munitions pyrotechniques en fonction des besoins de la mission opérationnelle',
            'url' => NULL,
            'ordre' => 5,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C13',
            'libelle_court' => 'C13 - Superviser la mise en œuvre des armes et leurres A.S.M.',
            'libelle_long' => 'Superviser la mise en œuvre des armes et leurres A.S.M.',
            'url' => NULL,
            'ordre' => 6,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C15',
            'libelle_court' => 'C15 - Réaliser des maintenances PREV et CORR des senseurs OPS et des équipements associés en environnement automatisé et informatisé',
            'libelle_long' => 'Réaliser des maintenances PREV et CORR des senseurs OPS et des équipements associés en environnement automatisé et informatisé',
            'url' => NULL,
            'ordre' => 7,
            'created_at' => now(),
            'updated_at' => now(),
          ],
          [
            'uuid' => Str::uuid()->toString(),
            'numero' => 'C19',
            'libelle_court' => 'C19 - EXA TC',
            'libelle_long' => 'EXA TC',
            'url' => NULL,
            'ordre' => 8,
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