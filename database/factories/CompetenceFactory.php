<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Competence;

class CompetenceFactory extends Factory
{
    protected $model = Competence::class;

    public function definition()
    {
        static $counter = 1;
        $libelle = 'Compétence';

        return [
            'uuid' => $this->faker->uuid,
            'code' => 'COMP-'.$counter,
            'libelle_court' => strtoupper(Str::limit($libelle, 3, '')).' '.$counter,
            'libelle_long' => $libelle.' '.$counter++.' : n°'.$this->faker->numberBetween(1, 11).', '.$this->faker->sentence(6),
            'parcours_type_id' => null,
            'ordre' => $counter,
            'color' => $this->faker->safeHexColor,
        ];
    }
}
