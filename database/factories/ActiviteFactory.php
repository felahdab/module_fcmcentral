<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCentral\Models\Activite;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransformationHistory>
 */
class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // Compteur
    private static $counter = 1;
    // Libelle
    private static $libelle = "Activite";


    public function definition()
    {
        //$libelle = fake()->text();
        $libcourt   =  strtoupper(Str::limit(self::$libelle, 3,' ')).' '.self::$counter;
        $liblong    =  self::$libelle.' '.self::$counter++.' : n°'.$this->faker->numberBetween(1,11).',  '.$this->faker->sentence(6);

        return [
            //"libelle_long" => $libelle,
            // "libelle_court" => Str::limit($libelle, 10),
            // "url" => fake()->url(),
            "libelle_court" => $libcourt,
            "libelle_long"  => $liblong,
            "uuid"          => $this->faker->uuid,
            "url"           => $this->faker->url,
            "type_activite" => "STAGE",
            "duree"         => $this->faker->randomElement(['1 Semaine','2 Semaines','1 mois','2 mois']),
            "duree_validite"=> $this->faker->randomElement(['6 mois','1 ans','2 ans','3 ans']),
            "prerequis"     => $this->faker->randomElement(['Aucun','BAT','Anglais','Bac+3']),
            "coeff"         => $this->faker->randomFloat(2,0,10),
        ];
    }
}