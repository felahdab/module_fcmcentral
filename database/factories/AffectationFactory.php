<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Affectation;

class AffectationFactory extends Factory
{
    protected $model = Affectation::class;

    public function definition()
    {
        static $counter = 1;
        $libelle = "Affectation";
        return [
            'uuid'          => $this->faker->uuid,
            'code'          => 'AFF-' . $counter,
            'libelle_court' => strtoupper(Str::limit($libelle, 3, '')) . ' ' . $counter,
            'libelle_long'  => $libelle . ' ' . $counter++ . ' : n°' . $this->faker->numberBetween(1, 11) . ', ' . $this->faker->sentence(6),
            'ordre'         => $counter,
            'color'         => $this->faker->safeHexColor,
        ];
    }
}
