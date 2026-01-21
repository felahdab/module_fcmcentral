<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Parcours;

class ParcoursSerialiseFactory extends Factory
{
    protected $model = ParcoursSerialise::class;

    public function definition(): array
    {
        return [
            'libelle_court' => $this->faker->words(3, true),
            'libelle_long' => $this->faker->sentence(),
            'version' => 1,
            'date_debut' => now()->subDays(30)->format('Y-m-d'),
            'date_fin' => null,
            'parcours_id' => Parcours::factory(),
            'parcours' => $this->generateDefaultParcoursStructure(),
        ];
    }

    /**
     * Version clôturée
     */
    public function cloturee(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_fin' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Version active (non clôturée)
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_fin' => null,
        ]);
    }

    /**
     * Avec une version spécifique
     */
    public function version(int $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }

    /**
     * Avec une date de début spécifique
     */
    public function dateDebut(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date_debut' => $date,
        ]);
    }

    /**
     * Avec un parcours spécifique
     */
    public function pourParcours(Parcours|int $parcours): static
    {
        $parcoursId = $parcours instanceof Parcours ? $parcours->id : $parcours;
        
        return $this->state(fn (array $attributes) => [
            'parcours_id' => $parcoursId,
        ]);
    }

    /**
     * Avec une structure de parcours complète
     */
    public function avecStructureComplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'parcours' => $this->generateCompleteParcoursStructure(),
        ]);
    }

    /**
     * Avec une structure minimale
     */
    public function avecStructureMinimale(): static
    {
        return $this->state(fn (array $attributes) => [
            'parcours' => $this->generateMinimalParcoursStructure(),
        ]);
    }

    /**
     * Avec plusieurs objectifs
     */
    public function avecNObjectifs(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $parcours = $attributes['parcours'] ?? $this->generateDefaultParcoursStructure();
            
            if (empty($parcours['affectations'])) {
                $parcours['affectations'] = [[
                    'id' => $this->faker->randomNumber(),
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => 'Affectation',
                    'libelle_long' => 'Affectation test',
                    'color' => '#3B82F6',
                    'pivot' => ['ordre' => 1],
                    'competences' => [],
                ]];
            }

            if (empty($parcours['affectations'][0]['competences'])) {
                $parcours['affectations'][0]['competences'] = [[
                    'id' => $this->faker->randomNumber(),
                    'uuid' => $this->faker->uuid(),
                    'code' => 'COMP01',
                    'libelle_court' => 'Compétence',
                    'libelle_long' => 'Compétence test',
                    'color' => '#10B981',
                    'ordre' => 1,
                    'competenceSavoirfaires' => [],
                ]];
            }

            $objectifs = [];
            for ($i = 1; $i <= $count; $i++) {
                $objectifs[] = [
                    'id' => $this->faker->randomNumber(),
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => "Objectif {$i}",
                    'description' => "Description objectif {$i}",
                    'ordre' => $i,
                    'code' => sprintf('OBJ%02d', $i),
                    'color' => '#10B981',
                ];
            }

            $parcours['affectations'][0]['competences'][0]['competenceSavoirfaires'] = [[
                'id' => $this->faker->randomNumber(),
                'uuid' => $this->faker->uuid(),
                'ordre' => 1,
                'heures' => 40,
                'jours' => 5,
                'libelle_court' => 'Savoir-faire',
                'savoirfaire' => null,
                'savoirfaire_type' => null,
                'objectifs' => $objectifs,
            ]];

            return ['parcours' => $parcours];
        });
    }

    /**
     * Structure par défaut du parcours
     */
    private function generateDefaultParcoursStructure(): array
    {
        return [
            'id' => $this->faker->randomNumber(),
            'uuid' => $this->faker->uuid(),
            'code' => $this->faker->bothify('PARC###'),
            'libelle_court' => $this->faker->words(3, true),
            'libelle_long' => $this->faker->sentence(),
            'parcours_type_id' => $this->faker->randomNumber(),
            'color' => $this->faker->hexColor(),
            'affectations' => [],
        ];
    }

    /**
     * Structure minimale avec une affectation
     */
    private function generateMinimalParcoursStructure(): array
    {
        $structure = $this->generateDefaultParcoursStructure();
        
        $structure['affectations'] = [
            [
                'id' => $this->faker->randomNumber(),
                'uuid' => $this->faker->uuid(),
                'libelle_court' => $this->faker->words(2, true),
                'libelle_long' => $this->faker->sentence(),
                'color' => $this->faker->hexColor(),
                'pivot' => ['ordre' => 1],
                'competences' => [],
            ]
        ];

        return $structure;
    }

    /**
     * Structure complète avec affectations, compétences, savoir-faire et objectifs
     */
    private function generateCompleteParcoursStructure(): array
    {
        $structure = $this->generateDefaultParcoursStructure();
        
        $structure['affectations'] = [
            [
                'id' => $this->faker->randomNumber(),
                'uuid' => $this->faker->uuid(),
                'libelle_court' => 'Affectation 1',
                'libelle_long' => 'Première affectation du parcours',
                'color' => '#3B82F6',
                'pivot' => ['ordre' => 1],
                'competences' => [
                    [
                        'id' => $this->faker->randomNumber(),
                        'uuid' => $this->faker->uuid(),
                        'code' => 'COMP01',
                        'libelle_court' => 'Compétence 1',
                        'libelle_long' => 'Première compétence',
                        'color' => '#10B981',
                        'ordre' => 1,
                        'competenceSavoirfaires' => [
                            [
                                'id' => $this->faker->randomNumber(),
                                'uuid' => $this->faker->uuid(),
                                'ordre' => 1,
                                'heures' => 40,
                                'jours' => 5,
                                'libelle_court' => 'Savoir-faire 1',
                                'savoirfaire' => [
                                    'id' => $this->faker->randomNumber(),
                                    'uuid' => $this->faker->uuid(),
                                    'code' => 'SF01',
                                    'libelle_court' => 'Savoir-faire principal',
                                    'libelle_long' => 'Description du savoir-faire',
                                    'competence_id' => $this->faker->randomNumber(),
                                    'color' => '#10B981',
                                ],
                                'savoirfaire_type' => [
                                    'id' => $this->faker->randomNumber(),
                                    'code' => 'TYPE01',
                                    'libelle_court' => 'Type de savoir-faire',
                                ],
                                'objectifs' => [
                                    [
                                        'id' => $this->faker->randomNumber(),
                                        'uuid' => $this->faker->uuid(),
                                        'libelle_court' => 'Objectif 1',
                                        'description' => 'Description de l\'objectif',
                                        'ordre' => 1,
                                        'code' => 'OBJ01',
                                        'color' => '#10B981',
                                    ],
                                    [
                                        'id' => $this->faker->randomNumber(),
                                        'uuid' => $this->faker->uuid(),
                                        'libelle_court' => 'Objectif 2',
                                        'description' => 'Description du second objectif',
                                        'ordre' => 2,
                                        'code' => 'OBJ02',
                                        'color' => '#10B981',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $structure;
    }
}
