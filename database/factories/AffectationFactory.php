<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCentral\Models\Affectation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransformationHistory>
 */
class AffectationFactory extends Factory
{
    protected $model = Affectation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     // Compteur
     private static $counter = 1;
     // Libelle
     private static $libelle = "Affectation";


    public function definition()
    {
        //$libelle = fake()->text();
        $libcourt   =  strtoupper(Str::limit(self::$libelle, 3,' ')).' '.self::$counter;
        $liblong    =  self::$libelle.' '.self::$counter++.' : n°'.$this->faker->numberBetween(1,11).',  '.$this->faker->sentence(6);

        return [
            
            "libelle_court" => $libcourt,
            "libelle_long"  => $liblong,
            "uuid"          =>  $this->faker->uuid,
            "ordre"         => self::$counter,

        ];
    }
}