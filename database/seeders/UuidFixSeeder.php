<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UuidFixSeeder extends Seeder
{
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider les tables
        DB::table('fcmcentral_type_etats')->truncate();
        DB::table('fcmcentral_type_notes')->truncate();
        DB::table('fcmcentral_savoirfaire_types')->truncate();
        DB::table('fcmcentral_parcours_types')->truncate();
        DB::table('fcmcentral_domaines')->truncate();

        // Réinsérer les domaines
        $domaines = [
            [
                
                'uuid' => 'DOM11111-1111-1111-1111-111111111111',
                'code' => 'GEN',
                'libelle_long' => 'GENOPS',
                'libelle_court' => 'GENOPS',
                'ordre' => 1,
                'color' => 'gray',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM22222-2222-2222-2222-222222222222',
                'code' => 'LSM',
                'libelle_long' => 'LSM',
                'libelle_court' => 'LSM',
                'ordre' => 2,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM33333-3333-3333-3333-333333333333',
                'code' => 'SCI',
                'libelle_long' => 'SCIENTIFIQUE',
                'libelle_court' => 'SCIENTIFIQUE',
                'ordre' => 3,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM44444-1111-1111-1111-111111111111',
                'code' => 'ANG',
                'libelle_long' => 'ANGLAIS',
                'libelle_court' => 'ANGLAIS',
                'ordre' => 4,
                'color' => 'danger',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM55555-1111-1111-1111-111111111111',
                'code' => 'LOG',
                'libelle_long' => 'LOG',
                'libelle_court' => 'LOG',
                'ordre' => 5,
                'color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM66666-1111-1111-1111-111111111111',
                'code' => 'ENC',
                'libelle_long' => 'ENCADREMENT',
                'libelle_court' => 'ENCADREMENT',
                'ordre' => 6,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'DOM77777-1111-1111-1111-111111111111',
                'code' => 'AUT',
                'libelle_long' => 'AUTRE',
                'libelle_court' => 'AUTRE',
                'ordre' => 7,
                'color' => 'gray',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('fcmcentral_domaines')->insert($domaines);

        // Réinsérer les savoir-faire types
        $savoirfaireTypes = [
            [
                'uuid' => 'SFT11111-1111-1111-1111-111111111111',
                'code' => 'C',
                'libelle_long' => 'Acquis par Compagnonnage',
                'libelle_court' => 'Compagnonnage',
                'ordre' => 1,
                'color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'SFT22222-2222-2222-2222-222222222222',
                'code' => 'S',
                'libelle_long' => 'Acquis par Stage',
                'libelle_court' => 'Stage',
                'ordre' => 2,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'SFT33333-3333-3333-3333-333333333333',
                'code' => 'EAD',
                'libelle_long' => 'Acquis par Enseignement a Distance',
                'libelle_court' => 'Enseignement a Distance',
                'ordre' => 3,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('fcmcentral_savoirfaire_types')->insert($savoirfaireTypes);

        // Réinsérer les types de parcours
        $parcoursTypes = [
            [
                'uuid' => 'PT111111-1111-1111-1111-111111111111',
                'code' => 'DEASM',
                'libelle_long' => 'DEASM',
                'libelle_court' => 'DEASM',
                'ordre' => 1,
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => 'PT222222-2222-2222-2222-222222222222',
                'code' => 'ELECT',
                'libelle_long' => 'ELECT',
                'libelle_court' => 'ELECT',
                'ordre' => 2,
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('fcmcentral_parcours_types')->insert($parcoursTypes);

        // Réinsérer les types d'états
        $typeEtats = [
            [
                'uuid' => 'ETAT1111-1111-1111-1111-111111111111',
                'code' => 'GEL_PAU',
                'libelle_court' => 'En Pause',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ];
        DB::table('fcmcentral_type_etats')->insert($typeEtats);

        // Réinsérer les types de notes
        $typeNotes = [
            [
                'uuid' => 'NOTE1111-1111-1111-1111-111111111111',
                'code' => 'COMMENTAIRE',
                'libelle_court' => 'Commentaire',
                'created_at' => now(),
                'updated_at' => now(),
            ],
           
        ];
        DB::table('fcmcentral_type_notes')->insert($typeNotes);

        // Réactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ SeedTypeSeeder terminé avec UUID fixes.');
    }
}

