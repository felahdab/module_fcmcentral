<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Objectif;

class ObjectifFactory extends Factory
{
    protected $model = Objectif::class;

    public function definition()
    {
        static $counter = 1;
        $libelle = 'Objectif';

        return [
            'competence_savoirfaire_id' => CompetenceSavoirfaire::factory(),
            'uuid' => $this->faker->uuid,
            'code' => 'OBJ-'.$counter,
            'libelle_court' => strtoupper(Str::limit($libelle, 3, '')).' '.$counter,
            'libelle_long' => $libelle.' '.$counter++.' : n°'.$this->faker->numberBetween(1, 11).', '.$this->faker->sentence(6),
            'coeff' => $this->faker->randomFloat(2, 1, 5),
            'ordre' => $counter,
        ];
    }
}
