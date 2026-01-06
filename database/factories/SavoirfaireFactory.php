<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Savoirfaire;

class SavoirfaireFactory extends Factory
{
    protected $model = Savoirfaire::class;

    public function definition()
    {
        static $counter = 1;
        $libelle = "Savoir Faire";
        return [
            'uuid'          => $this->faker->uuid,
            'code'          => 'SF-' . $counter,
            'libelle_court' => strtoupper(Str::limit($libelle, 3, '')) . ' ' . $counter,
            'libelle_long'  => $libelle . ' ' . $counter++ . ' : n°' . $this->faker->numberBetween(1, 11) . ', ' . $this->faker->sentence(6),
            'code_sicomp'   => $this->faker->optional()->bothify('SICOMP-###'),
            'same'          => $this->faker->optional()->word,
            'ordre'         => $counter,
            'competence_id' => null,
            'domaine_id'    => null,
            'color'         => $this->faker->safeHexColor,
        ];
    }
}
