<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;

class CompetenceSavoirfaireFactory extends Factory
{
    protected $model = CompetenceSavoirfaire::class;

    public function definition()
    {
        static $counter = 1;

        return [
            'uuid' => $this->faker->uuid,
            'competence_id' => null,
            'savoirfaire_id' => null,
            'savoirfaire_type_id' => null,
            'heures' => $this->faker->numberBetween(1, 100),
            'jours' => $this->faker->numberBetween(1, 30),
            'libelle_court' => 'CSF '.$counter,
            'ordre' => $counter++,
        ];
    }
}
