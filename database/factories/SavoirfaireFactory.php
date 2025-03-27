<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCentral\Models\Savoirfaire;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransformationHistory>
 */
class SavoirfaireFactory extends Factory
{
    protected $model = Savoirfaire::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


      // Compteur
    private static $counter = 1;
    // Libelle
    private static $libelle = "Savoir Faire";


    public function definition()
    {
        //$libelle = fake()->text();
        $libcourt   =  strtoupper(Str::limit('SF', 3,' ')).' '.self::$counter;
        $liblong    =  self::$libelle.' '.self::$counter++.' : n°'.$this->faker->numberBetween(1,11).',  '.$this->faker->sentence(6);

        return [
            
            "libelle_court" => $libcourt,
            "libelle_long"  => $liblong,
            "url" => fake()->url(),
            "uuid"          =>  $this->faker->uuid,
            "code_sicomp"   => "code SICOMP",
            "mod_acquis"   => $this->faker->randomElement(['OUI','NON']),
            "ordre"         => $this->faker->numberBetween(0,10),
           // "niveau" => "M"
        ];
    }
}