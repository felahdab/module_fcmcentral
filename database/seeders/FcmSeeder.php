<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Modules\FcmCentral\Models\Activite;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Fonction;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Savoirfaire;

class FcmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Creer 10 Activites (A))
        $activites = Activite::factory()->count(10)->create();

        // Creer 10 Competences (C))
        $competences = Competence::factory()->count(10)->create();

        // Creer 10 Fonctions (F)
        $fonctions = Fonction::factory()->count(10)->create();

        // Creer 10 Parcours (P)
        $parcours = Parcours::factory()->count(10)->create();

        // Creer 10 Savoir Faire (SF)
        $savoirfaires = Savoirfaire::factory()->count(10)->create();


        // Associer Many To Many F P
        $fonctions->each(function ($fonction) use ($parcours){
            $fonction->parcours()->attach(
                $parcours->random(rand(2,5))->pluck('id')->toArray()
            );
        });

        // Associer Many To Many C F
        $competences->each(function ($competence) use ($fonctions){
            $competence->fonctions()->attach(
                $fonctions->random(rand(2,5))->pluck('id')->toArray()
            );
        });

        // Associer Many To Many SF C
        $savoirfaires->each(function ($savoirfaire) use ($competences){
            $savoirfaire->competences()->attach(
                $competences->random(rand(2,5))->pluck('id')->toArray()
            );
        });

        // Associer Many To Many A SF
        $activites->each(function ($activite) use ($savoirfaires){
            $activite->savoirfaires()->attach(
                $savoirfaires->random(rand(2,5))->pluck('id')->toArray(),
                [
                    'coeff' => rand(1,10),
                    'duree' => rand(10,120),
                    'ordre' => rand (1,5),
                    'data'  => json_encode(['note'=>'Exemple de données']),
                ]
            );
        });
      


    }
}