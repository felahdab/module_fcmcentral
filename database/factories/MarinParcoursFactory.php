<?php

namespace Modules\FcmCentral\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\ParcoursSerialise;

class MarinParcoursFactory extends Factory
{
    protected $model = MarinParcours::class;

    public function definition(): array
    {
        return [
            'fcmmarin_id' => FcmMarin::factory(),
            'parcoursserialise_id' => ParcoursSerialise::factory(),
            'taux_global' => $this->faker->numberBetween(0, 100),
            'taux_stage' => $this->faker->numberBetween(0, 100),
            'taux_activite' => $this->faker->numberBetween(0, 100),
            'parcoursmarin' => $this->generateDefaultParcoursMarinStructure(),
        ];
    }

    /**
     * Avec un FcmMarin spécifique
     */
    public function pourFcmMarin(FcmMarin|int $fcmMarin): static
    {
        $fcmMarinId = $fcmMarin instanceof FcmMarin ? $fcmMarin->id : $fcmMarin;
        
        return $this->state(fn (array $attributes) => [
            'fcmmarin_id' => $fcmMarinId,
        ]);
    }

    /**
     * Avec un ParcoursSerialise spécifique
     */
    public function pourParcoursSerialise(ParcoursSerialise|int $parcoursSerialise): static
    {
        $parcoursSerialiseId = $parcoursSerialise instanceof ParcoursSerialise 
            ? $parcoursSerialise->id 
            : $parcoursSerialise;
        
        return $this->state(fn (array $attributes) => [
            'parcoursserialise_id' => $parcoursSerialiseId,
        ]);
    }

    /**
     * Avec des taux spécifiques
     */
    public function avecTaux(int $global = null, int $stage = null, int $activite = null): static
    {
        return $this->state(function (array $attributes) use ($global, $stage, $activite) {
            $state = [];
            
            if ($global !== null) {
                $state['taux_global'] = $global;
            }
            if ($stage !== null) {
                $state['taux_stage'] = $stage;
            }
            if ($activite !== null) {
                $state['taux_activite'] = $activite;
            }
            
            return $state;
        });
    }

    /**
     * Parcours complété à 100%
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'taux_global' => 100,
            'taux_stage' => 100,
            'taux_activite' => 100,
            'parcoursmarin' => $this->generateCompletedParcoursMarinStructure(),
        ]);
    }

    /**
     * Parcours en cours (50%)
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'taux_global' => 50,
            'taux_stage' => 50,
            'taux_activite' => 50,
            'parcoursmarin' => $this->generateInProgressParcoursMarinStructure(),
        ]);
    }

    /**
     * Parcours non commencé (0%)
     */
    public function nonCommence(): static
    {
        return $this->state(fn (array $attributes) => [
            'taux_global' => 0,
            'taux_stage' => 0,
            'taux_activite' => 0,
            'parcoursmarin' => $this->generateDefaultParcoursMarinStructure(),
        ]);
    }

    /**
     * Avec une structure de parcours marin personnalisée
     */
    public function avecParcoursMarinData(array $data): static
    {
        return $this->state(fn (array $attributes) => [
            'parcoursmarin' => $data,
        ]);
    }

    /**
     * Avec N affectations
     */
    public function avecNAffectations(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $affectations = [];
            
            for ($i = 1; $i <= $count; $i++) {
                $affectations[] = [
                    'id' => $i,
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => "Affectation {$i}",
                    'completed' => $this->faker->boolean(),
                    'taux' => $this->faker->numberBetween(0, 100),
                    'competences' => [],
                ];
            }

            return [
                'parcoursmarin' => [
                    'affectations' => $affectations,
                ],
            ];
        });
    }

    /**
     * Avec N objectifs
     */
    public function avecNObjectifs(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $objectifs = [];
            
            for ($i = 1; $i <= $count; $i++) {
                $objectifs[] = [
                    'id' => $i,
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => "Objectif {$i}",
                    'code' => sprintf('OBJ%02d', $i),
                    'completed' => $i <= ($count / 2), // 50% complétés
                    'date_validation' => $i <= ($count / 2) ? now()->subDays($count - $i)->format('Y-m-d') : null,
                ];
            }

            return [
                'parcoursmarin' => [
                    'affectations' => [
                        [
                            'id' => 1,
                            'uuid' => $this->faker->uuid(),
                            'libelle_court' => 'Affectation principale',
                            'competences' => [
                                [
                                    'id' => 1,
                                    'uuid' => $this->faker->uuid(),
                                    'libelle_court' => 'Compétence principale',
                                    'competenceSavoirfaires' => [
                                        [
                                            'id' => 1,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Savoir-faire principal',
                                            'objectifs' => $objectifs,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Structure par défaut du parcours marin
     */
    private function generateDefaultParcoursMarinStructure(): array
    {
        return [
            'affectations' => [
                [
                    'id' => 1,
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => 'Affectation 1',
                    'completed' => false,
                    'taux' => 0,
                    'competences' => [
                        [
                            'id' => 1,
                            'uuid' => $this->faker->uuid(),
                            'libelle_court' => 'Compétence 1',
                            'completed' => false,
                            'competenceSavoirfaires' => [
                                [
                                    'id' => 1,
                                    'uuid' => $this->faker->uuid(),
                                    'libelle_court' => 'Savoir-faire 1',
                                    'objectifs' => [
                                        [
                                            'id' => 1,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Objectif 1',
                                            'code' => 'OBJ01',
                                            'completed' => false,
                                            'date_validation' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Structure d'un parcours marin complété
     */
    private function generateCompletedParcoursMarinStructure(): array
    {
        return [
            'affectations' => [
                [
                    'id' => 1,
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => 'Affectation 1',
                    'completed' => true,
                    'taux' => 100,
                    'competences' => [
                        [
                            'id' => 1,
                            'uuid' => $this->faker->uuid(),
                            'libelle_court' => 'Compétence 1',
                            'completed' => true,
                            'competenceSavoirfaires' => [
                                [
                                    'id' => 1,
                                    'uuid' => $this->faker->uuid(),
                                    'libelle_court' => 'Savoir-faire 1',
                                    'objectifs' => [
                                        [
                                            'id' => 1,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Objectif 1',
                                            'code' => 'OBJ01',
                                            'completed' => true,
                                            'date_validation' => now()->subDays(10)->format('Y-m-d'),
                                        ],
                                        [
                                            'id' => 2,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Objectif 2',
                                            'code' => 'OBJ02',
                                            'completed' => true,
                                            'date_validation' => now()->subDays(5)->format('Y-m-d'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Structure d'un parcours marin en cours
     */
    private function generateInProgressParcoursMarinStructure(): array
    {
        return [
            'affectations' => [
                [
                    'id' => 1,
                    'uuid' => $this->faker->uuid(),
                    'libelle_court' => 'Affectation 1',
                    'completed' => false,
                    'taux' => 50,
                    'competences' => [
                        [
                            'id' => 1,
                            'uuid' => $this->faker->uuid(),
                            'libelle_court' => 'Compétence 1',
                            'completed' => false,
                            'competenceSavoirfaires' => [
                                [
                                    'id' => 1,
                                    'uuid' => $this->faker->uuid(),
                                    'libelle_court' => 'Savoir-faire 1',
                                    'objectifs' => [
                                        [
                                            'id' => 1,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Objectif 1',
                                            'code' => 'OBJ01',
                                            'completed' => true,
                                            'date_validation' => now()->subDays(10)->format('Y-m-d'),
                                        ],
                                        [
                                            'id' => 2,
                                            'uuid' => $this->faker->uuid(),
                                            'libelle_court' => 'Objectif 2',
                                            'code' => 'OBJ02',
                                            'completed' => false,
                                            'date_validation' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
