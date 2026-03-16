<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;

class DeasmCompetenceSavoirfairesSeeder extends Seeder
{
    public function run(): void
    {
        $competences      = Competence::pluck('id', 'code');
        $savoirfaires     = Savoirfaire::pluck('id', 'code');
        $savoirfaireTypes = SavoirfaireType::pluck('id', 'code');

        $i = 1;

        $rows = [
            // AN 2
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1', 'competence' => 'C1', 'sf' => 'C1-SF1', 'type' => 'C', 'libelle_court' => '',                              'heures' => 19,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c1c1sf2', 'competence' => 'C1', 'sf' => 'C1-SF2', 'type' => 'C', 'libelle_court' => '',                              'heures' => 8.5,  'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c1c1sf3', 'competence' => 'C1', 'sf' => 'C1-SF3', 'type' => 'C', 'libelle_court' => '',                              'heures' => 4,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c2c2sf1', 'competence' => 'C2', 'sf' => 'C2-SF1', 'type' => 'C', 'libelle_court' => '',                              'heures' => 42,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c2c2sf2', 'competence' => 'C2', 'sf' => 'C2-SF2', 'type' => 'C', 'libelle_court' => '',                              'heures' => 25,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c2c2sf3', 'competence' => 'C2', 'sf' => 'C2-SF3', 'type' => 'C', 'libelle_court' => '',                              'heures' => 6,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c2c2sf4', 'competence' => 'C2', 'sf' => 'C2-SF4', 'type' => 'C', 'libelle_court' => '',                              'heures' => 15,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c2c2sf5', 'competence' => 'C2', 'sf' => 'C2-SF5', 'type' => 'S', 'libelle_court' => 'FCM/THEORIE LAS NIVEAU 3',     'heures' => 35,   'jours' => 5],

            // AN 3
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c3c3sf1', 'competence' => 'C3', 'sf' => 'C3-SF1', 'type' => 'C', 'libelle_court' => '',                              'heures' => 16,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c3c3sf2', 'competence' => 'C3', 'sf' => 'C3-SF2', 'type' => 'C', 'libelle_court' => '',                              'heures' => 10,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c3c3sf3', 'competence' => 'C3', 'sf' => 'C3-SF3', 'type' => 'C', 'libelle_court' => '',                              'heures' => 4.75, 'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c3c3sf4', 'competence' => 'C3', 'sf' => 'C3-SF4', 'type' => 'C', 'libelle_court' => '',                              'heures' => 2,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c3c3sf5', 'competence' => 'C3', 'sf' => 'C3-SF5', 'type' => 'S', 'libelle_court' => 'FCM/LSM NIVEAU 2 + ELAB + ENVIRO + MENACE SOUM', 'heures' => 70, 'jours' => 10],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf1', 'competence' => 'C4', 'sf' => 'C4-SF1', 'type' => 'C', 'libelle_court' => '',                              'heures' => 10,   'jours' => 0],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf2', 'competence' => 'C4', 'sf' => 'C4-SF2', 'type' => 'C', 'libelle_court' => '',                              'heures' => 5,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf3', 'competence' => 'C4', 'sf' => 'C4-SF3', 'type' => 'C', 'libelle_court' => '',                              'heures' => 20,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa2c4c4sf3', 'competence' => 'C4', 'sf' => 'C4-SF3', 'type' => 'S', 'libelle_court' => 'FCM/LSM NIVEAU 3',              'heures' => 0,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf4', 'competence' => 'C4', 'sf' => 'C4-SF4', 'type' => 'C', 'libelle_court' => '',                              'heures' => 6,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf5', 'competence' => 'C4', 'sf' => 'C4-SF5', 'type' => 'C', 'libelle_court' => '',                              'heures' => 9,    'jours' => 0],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf6', 'competence' => 'C4', 'sf' => 'C4-SF6', 'type' => 'S', 'libelle_court' => 'FCM/LSM NIVEAU 3',              'heures' => 0,    'jours' => null],

            // AN 4
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c4c4sf7', 'competence' => 'C4', 'sf' => 'C4-SF7', 'type' => 'C', 'libelle_court' => 'Evaluation bord CASM',          'heures' => 4,    'jours' => 0.5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa2c4c4sf7', 'competence' => 'C4', 'sf' => 'C4-SF7', 'type' => 'S', 'libelle_court' => 'TP DIVENT',                     'heures' => 7,    'jours' => 1],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c5c5sf1', 'competence' => 'C5', 'sf' => 'C5-SF1', 'type' => 'S', 'libelle_court' => 'FCM/SDC-LDT',                   'heures' => 35,   'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c5c5sf2', 'competence' => 'C5', 'sf' => 'C5-SF2', 'type' => 'S', 'libelle_court' => 'FCM/ANGLAIS NIVEAU 2',          'heures' => 35,   'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c5c5sf3', 'competence' => 'C5', 'sf' => 'C5-SF3', 'type' => 'S', 'libelle_court' => 'FCM/ANGLAIS NIVEAU 3',          'heures' => 35,   'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf1', 'competence' => 'C6', 'sf' => 'C6-SF1', 'type' => 'EAD', 'libelle_court' => 'EAD LOG',                     'heures' => 10,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf2', 'competence' => 'C6', 'sf' => 'C6-SF2', 'type' => 'EAD', 'libelle_court' => '',                            'heures' => 0,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf3', 'competence' => 'C6', 'sf' => 'C6-SF3', 'type' => 'EAD', 'libelle_court' => 'FCM GESTION DE MUNITIONS',    'heures' => 16,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf4', 'competence' => 'C6', 'sf' => 'C6-SF4', 'type' => 'EAD', 'libelle_court' => 'FCM GESTION DE MUNITIONS',    'heures' => 0,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf5', 'competence' => 'C6', 'sf' => 'C6-SF5', 'type' => 'S',   'libelle_court' => 'FCM/LOG NIVEAU 3 (+ SIGLE EMB)', 'heures' => 0, 'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c6c6sf6', 'competence' => 'C6', 'sf' => 'C6-SF6', 'type' => 'S',   'libelle_court' => 'FCM/LOG NIVEAU 3 (+ SIGLE EMB)', 'heures' => 0, 'jours' => null],

            // AN 5
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c7c7sf1', 'competence' => 'C7', 'sf' => 'C7-SF1', 'type' => 'C',   'libelle_court' => '',                             'heures' => 4,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c7c7sf2', 'competence' => 'C7', 'sf' => 'C7-SF2', 'type' => 'C',   'libelle_court' => '',                             'heures' => 15,   'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c7c7sf3', 'competence' => 'C7', 'sf' => 'C7-SF3', 'type' => 'S',   'libelle_court' => 'STAGE FCM/LSM NIVEAU 3',       'heures' => 70,   'jours' => 10],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf1', 'competence' => 'C8', 'sf' => 'C8-SF1', 'type' => 'EAD', 'libelle_court' => 'EAD LOG',                     'heures' => 0,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf2', 'competence' => 'C8', 'sf' => 'C8-SF2', 'type' => 'EAD', 'libelle_court' => 'ATRIUM',                      'heures' => 35,   'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa2c8c8sf2', 'competence' => 'C8', 'sf' => 'C8-SF2', 'type' => 'S',   'libelle_court' => 'FCM/MCO ELECTRONIQUE NIVEAU2', 'heures' => 35,   'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf3', 'competence' => 'C8', 'sf' => 'C8-SF3', 'type' => 'S',   'libelle_court' => 'FCM/LOG NIVEAU 3 (+ SIGLE EMB)', 'heures' => 34, 'jours' => 5],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf4', 'competence' => 'C8', 'sf' => 'C8-SF4', 'type' => 'S',   'libelle_court' => 'FCM/LOG NIVEAU 3 (+ SIGLE EMB)', 'heures' => 0,  'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf5', 'competence' => 'C8', 'sf' => 'C8-SF5', 'type' => 'S',   'libelle_court' => 'FCM/INFOR + FIBRES + LINUX NIVEAU 3', 'heures' => 28, 'jours' => 4],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf6', 'competence' => 'C8', 'sf' => 'C8-SF6', 'type' => 'S',   'libelle_court' => 'FCM/INFOR + FIBRES + LINUX NIVEAU 3', 'heures' => 0,  'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf7', 'competence' => 'C8', 'sf' => 'C8-SF7', 'type' => 'S',   'libelle_court' => '',                             'heures' => 0,    'jours' => null],
            ['uuid' => 'deasm1b1-1234-5678-cosf-11aa1c8c8sf8', 'competence' => 'C8', 'sf' => 'C8-SF8', 'type' => 'S',   'libelle_court' => '',                             'heures' => 0,    'jours' => null],
        ];

        $count = 0;

        foreach ($rows as $row) {
            $competenceId     = $competences[$row['competence']] ?? null;
            $savoirfaireId    = $savoirfaires[$row['sf']] ?? null;
            $savoirfaireTypeId = $savoirfaireTypes[$row['type']] ?? null;

            if (! $competenceId || ! $savoirfaireId || ! $savoirfaireTypeId) {
                $this->command->warn("⚠️  Données manquantes pour [{$row['uuid']}] — ignoré.");
                continue;
            }

            CompetenceSavoirfaire::firstOrCreate(
                ['uuid' => $row['uuid']],
                [
                    'competence_id'       => $competenceId,
                    'savoirfaire_id'      => $savoirfaireId,
                    'savoirfaire_type_id' => $savoirfaireTypeId,
                    'libelle_court'       => $row['libelle_court'],
                    'ordre'               => $i++,
                    'heures'              => $row['heures'],
                    'jours'               => $row['jours'],
                ]
            );

            $count++;
        }

        $this->command->info("✅ fcmcentral_competence_savoirfaires: {$count} enregistrements traités");
    }
}