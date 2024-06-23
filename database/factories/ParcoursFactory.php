<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCentral\Models\Parcours;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransformationHistory>
 */
class ParcoursFactory extends Factory
{
    protected $model = Parcours::class;

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