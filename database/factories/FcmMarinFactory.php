<?php

// filepath: Modules/FcmCentral/database/factories/FcmMarinFactory.php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\RH\Models\Marin;

/**
 * @extends Factory<FcmMarin>
 */
class FcmMarinFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FcmMarin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rh_marin_id' => Marin::factory(),
            'data' => [
                'preferences' => [
                    'notifications' => true,
                    'langue' => 'fr',
                ],
                'statut_fcm' => 'actif',
                'date_inscription' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'objectifs' => $this->faker->sentences(3, true),
            ],
            'cohorte_id' => null, // Sera assigné dans les tests si nécessaire
            'mentor_id' => null,  // Sera assigné dans les tests si nécessaire
        ];
    }

    /**
     * Indicate that the FcmMarin has a mentor.
     */
    public function avecMentor(): static
    {
        return $this->state(fn (array $attributes) => [
            'mentor_id' => Marin::factory(),
        ]);
    }

    /**
     * Indicate that the FcmMarin belongs to a cohorte.
     */
    public function avecCohorte(): static
    {
        return $this->state(fn (array $attributes) => [
            'cohorte_id' => Cohorte::factory(),
        ]);
    }

    /**
     * Indicate that the FcmMarin has both mentor and cohorte.
     */
    public function complet(): static
    {
        return $this->state(fn (array $attributes) => [
            'mentor_id' => Marin::factory(),
            'cohorte_id' => Cohorte::factory(),
        ]);
    }

    /**
     * Configure the factory for a specific mentor.
     */
    public function pourMentor(Marin $mentor): static
    {
        return $this->state(fn (array $attributes) => [
            'mentor_id' => $mentor->id,
        ]);
    }

    /**
     * Configure the factory for a specific cohorte.
     */
    public function pourCohorte(Cohorte $cohorte): static
    {
        return $this->state(fn (array $attributes) => [
            'cohorte_id' => $cohorte->id,
        ]);
    }

    /**
     * Configure the factory for a specific marin RH.
     */
    public function pourMarin(Marin $marin): static
    {
        return $this->state(fn (array $attributes) => [
            'rh_marin_id' => $marin->id,
        ]);
    }

    /**
     * Create an FcmMarin with advanced progression data.
     */
    public function avecProgression(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'progression' => [
                    'niveau_actuel' => $this->faker->numberBetween(1, 5),
                    'competences_validees' => $this->faker->numberBetween(0, 15),
                    'heures_formation' => $this->faker->numberBetween(0, 200),
                    'derniere_activite' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                ],
                'evaluations' => [
                    'note_globale' => $this->faker->randomFloat(2, 0, 20),
                    'commentaires' => $this->faker->sentences(2, true),
                ],
            ]),
        ]);
    }

    /**
     * Create an inactive FcmMarin.
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'statut_fcm' => 'inactif',
                'date_desactivation' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            ]),
        ]);
    }
}

// // Dans vos tests

// // ✅ Créer un FcmMarin simple
// $fcmMarin = FcmMarin::factory()->create();

// // ✅ Créer avec mentor
// $fcmMarin = FcmMarin::factory()->avecMentor()->create();

// // ✅ Créer avec cohorte
// $fcmMarin = FcmMarin::factory()->avecCohorte()->create();

// // ✅ Créer complet (mentor + cohorte)
// $fcmMarin = FcmMarin::factory()->complet()->create();

// // ✅ Créer pour un mentor spécifique
// $mentor = Marin::factory()->create();
// $fcmMarin = FcmMarin::factory()->pourMentor($mentor)->create();

// // ✅ Créer pour une cohorte spécifique
// $cohorte = Cohorte::factory()->create();
// $fcmMarin = FcmMarin::factory()->pourCohorte($cohorte)->create();

// // ✅ Créer avec progression
// $fcmMarin = FcmMarin::factory()->avecProgression()->create();

// // ✅ Créer inactif
// $fcmMarin = FcmMarin::factory()->inactif()->create();

// // ✅ Combinaisons
// $fcmMarin = FcmMarin::factory()
//     ->avecMentor()
//     ->avecCohorte()
//     ->avecProgression()
//     ->create();

// // ✅ Créer plusieurs
// $fcmMarins = FcmMarin::factory()->count(5)->avecMentor()->create();
