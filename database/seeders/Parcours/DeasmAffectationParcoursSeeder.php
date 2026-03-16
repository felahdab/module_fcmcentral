<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\AffectationParcours;

class DeasmAffectationParcoursSeeder extends Seeder
{
    public function run(): void
    {
        $parcours    = Parcours::where('code', 'DEASM1')->firstOrFail();
        $affectations = Affectation::whereIn('code', ['A1', 'A2'])->pluck('id', 'code');

        $data = [
            ['affectation_code' => 'A1', 'ordre' => 1],
            ['affectation_code' => 'A2', 'ordre' => 2],
        ];

        foreach ($data as $item) {
            $affectationId = $affectations[$item['affectation_code']] ?? null;

            if (! $affectationId) {
                $this->command->warn("⚠️  Affectation [{$item['affectation_code']}] introuvable — ignoré.");
                continue;
            }

            AffectationParcours::firstOrCreate(
                [
                    'parcours_id'    => $parcours->id,
                    'affectation_id' => $affectationId,
                ],
                [
                    'ordre' => $item['ordre'],
                ]
            );
        }

        $this->command->info('✅ fcmcentral_affectation_parcours: ' . count($data) . ' enregistrements traités');
    }
}
