<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Affectation;

class DeasmCompetencesSeeder extends Seeder
{
    private function cleanString(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9 \-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }

    public function run(): void
    {
        $parcoursType = ParcoursType::where('code', 'DEASM')->firstOrFail();
        $affectations = Affectation::whereIn('code', ['A1', 'A2'])->pluck('id', 'code');

        $uuids = [
            'C1' => 'deasm1b1-1234-5678-comp-11aa11z0c01',
            'C2' => 'deasm1b1-1234-5678-comp-11aa11z0c02',
            'C3' => 'deasm1b1-1234-5678-comp-11aa11z0c03',
            'C4' => 'deasm1b1-1234-5678-comp-11aa11z0c04',
            'C5' => 'deasm1b1-1234-5678-comp-11aa11z0c05',
            'C6' => 'deasm1b1-1234-5678-comp-11aa11z0c06',
            'C7' => 'deasm1b1-1234-5678-comp-11aa11z0c07',
            'C8' => 'deasm1b1-1234-5678-comp-11aa11z0c08',
        ];

        $i = 1;

        $rows = [
            [
                'uuid'          => $uuids['C1'],
                'code'          => 'C1',
                'libelle_court' => $this->cleanString("C1 - Identifier son rôle dans la chaîne de commandement et des opérations"),
                'libelle_long'  => $this->cleanString("Identifier son rôle dans la chaîne de commandement et des opérations"),
                'color'         => 'success',
                'affectation'   => 'A1',
            ],
            [
                'uuid'          => $uuids['C2'],
                'code'          => 'C2',
                'libelle_court' => $this->cleanString("C2 - Identifier le spectre de la menace selon les vecteurs présents sur la zone"),
                'libelle_long'  => $this->cleanString("Identifier le spectre de la menace selon les vecteurs présents sur la zone"),
                'color'         => 'success',
                'affectation'   => 'A2',
            ],
            [
                'uuid'          => $uuids['C3'],
                'code'          => 'C3',
                'libelle_court' => $this->cleanString("C3 - Concevoir une classification technique en fonction de l'environnement et des senseurs ASM"),
                'libelle_long'  => $this->cleanString("Concevoir une classification technique en fonction de l'environnement et des senseurs ASM"),
                'color'         => 'info',
                'affectation'   => 'A1',
            ],
            [
                'uuid'          => $uuids['C4'],
                'code'          => 'C4',
                'libelle_court' => $this->cleanString("C4 - Exploiter le tâches ASM suivant le contexte opérationnel"),
                'libelle_long'  => $this->cleanString("Exploiter le tâches ASM suivant le contexte opérationnel"),
                'color'         => 'info',
                'affectation'   => 'A2',
            ],
            [
                'uuid'          => $uuids['C5'],
                'code'          => 'C5',
                'libelle_court' => $this->cleanString("C5 - Exploiter les données tactiques en contexte opérationnel interarmées ou interallié"),
                'libelle_long'  => $this->cleanString("Exploiter les données tactiques en contexte opérationnel interarmées ou interallié"),
                'color'         => 'purple',
                'affectation'   => 'A1',
            ],
            [
                'uuid'          => $uuids['C6'],
                'code'          => 'C6',
                'libelle_court' => $this->cleanString("C6 - Gérer les munitions pyrotechniques"),
                'libelle_long'  => $this->cleanString("Gérer les munitions pyrotechniques"),
                'color'         => 'brown',
                'affectation'   => 'A2',
            ],
            [
                'uuid'          => $uuids['C7'],
                'code'          => 'C7',
                'libelle_court' => $this->cleanString("C7 - Exploiter les armes et leurres A.S.M."),
                'libelle_long'  => $this->cleanString("Exploiter les armes et leurres A.S.M."),
                'color'         => 'success',
                'affectation'   => 'A2',
            ],
            [
                'uuid'          => $uuids['C8'],
                'code'          => 'C8',
                'libelle_court' => $this->cleanString("C8 - Réaliser des maintenances préventives et correctives des senseurs et des équipements associés"),
                'libelle_long'  => $this->cleanString("Réaliser des maintenances préventives et correctives des senseurs et des équipements associés"),
                'color'         => 'info',
                'affectation'   => 'A2',
            ],
        ];

        foreach ($rows as $row) {
            $affectationId = $affectations[$row['affectation']] ?? null;

            if (! $affectationId) {
                $this->command->warn("⚠️  Affectation [{$row['affectation']}] introuvable — {$row['code']} ignoré.");
                continue;
            }

            Competence::firstOrCreate(
                ['uuid' => $row['uuid']],
                [
                    'code'             => $row['code'],
                    'libelle_court'    => $row['libelle_court'],
                    'libelle_long'     => $row['libelle_long'],
                    'ordre'            => $i++,
                    'color'            => $row['color'],
                    'parcours_type_id' => $parcoursType->id,
                    'affectation_id'   => $affectationId,
                ]
            );
        }

        $this->command->info('✅ fcmcentral_competences: ' . count($rows) . ' enregistrements traités');
    }
}
