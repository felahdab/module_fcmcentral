<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursType;

class ParcoursFactory extends Factory
{
    protected $model = Parcours::class;

    public function definition()
    {
        static $counter = 1;
        $libelle = 'Parcours';

        return [
            'uuid' => $this->faker->uuid,
            'code' => 'PAR-'.$counter,
            'libelle_court' => strtoupper(Str::limit($libelle, 3, '')).' '.$counter,
            'libelle_long' => $libelle.' '.$counter++.' : n°'.$this->faker->numberBetween(1, 11).', '.$this->faker->sentence(6),
            'parcours_type_id' => ParcoursType::factory(),
            'ordre' => $counter,
            'color' => $this->faker->safeHexColor,
        ];
    }
}
