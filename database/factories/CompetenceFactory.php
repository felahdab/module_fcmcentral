<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCentral\Models\Competence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransformationHistory>
 */
class CompetenceFactory extends Factory
{
    protected $model = Competence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $libelle = fake()->text();

        return [
            "libelle_long" => $libelle,
            "libelle_court" => Str::limit($libelle, 10),
        ];
    }
}