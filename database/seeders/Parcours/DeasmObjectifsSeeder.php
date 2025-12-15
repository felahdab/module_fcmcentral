<?php
// filepath: Modules/FcmCentral/Database/Seeders/Parcours/DeasmObjectifsSeeder.php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeasmObjectifsSeeder extends Seeder
{
   
   // Bug Alpine Livewire dans le parcours, j'enleve les caracateres speciaux et accents  pour les seeders
   private  function cleanString($string) {
    return preg_replace('/[^A-Za-z0-9 \-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
}
   
   
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');


$array = [
    // An 2
    [
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Assurer le quart au central opération en interagissant avec les autres modules du CO."),
    ],
    [
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Distinguer les commandements opérationnels sous lesquels l'unité peut évoluer et l'impact sur la conduite des opérations."),
    ],
    [
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Analyser le PIM de l'unité et les réglementations associées aux zones maritimes traversées."),
    ],
    [
        'sf' => 'C1-SF2',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Identifier le contenu spécifique de chaque diapositive (N0 à N7) d'un BFG OPS."),
    ],
    [
        'sf' => 'C1-SF2',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Assurer la supervision et la présentation d'un BFG OPS (N0 à N7) devant le bord."),
    ],
    [
        'sf' => 'C1-SF3',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Maîtriser l'écosystème d'une CMI et le traitement des différents messages."),
    ],
    [
        'sf' => 'C1-SF3',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Exploiter un OPSGEN, un OPTASK et un FLOORPLAN."),
    ],
    [
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Gérer les règles de sécurité lors d'un exercice ASM ou de la mise en oeuvre d'une arme ASM."),
    ],
    [
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Diriger le module ASM lors d'une reprise de vue ou durant la lutte anti sous-marin."),
    ],
    [
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire l'action ASM dans le respect de la réglementation ZONEX."),
    ],
    [
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Distinguer les différents aspects de la propagation acoustique ainsi que les facteurs externes ou porteur pouvant l'influencer."),
    ],
    [
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Conduire l'ensemble du processus de réalisation de prédictions de portées jusqu'à la diffusion des messages ASM associés."),
    ],
    [
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Déduire les éléments tactiques des éléments environnementaux."),
    ],
    
    [
        'sf' => 'C3-SF3',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Employer les différentes formules de calcul et la reconnaissance des points remarquables nécessaires à l'élaboration des éléments but."),
    ],
    [
        'sf' => 'C3-SF3',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Résoudre des ELB."),
    ],
    [
        'sf' => 'C3-SF4',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Déterminer les caractéristiques d'un sous-marin et ses actions/réactions face à une menace ATBF."),
    ],

    // An 3
    [
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Employer les moyens AERO suivant les opérations en cours."),
    ],
    [
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la guerre électronique au différents domaines de luttes."),
    ],
    [
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire les opérations en LAN."),
    ],
    [
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire les opérations en LAA."),
    ],
    [
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la guerre électronique au différents domaines de luttes."),
    ],
    [
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire les opérations en LAN."),
    ],
    [
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire les opérations en LAA."),
    ],
    [
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O5',
        'libelle_court' => $this->cleanString("O5 - Conduire les opérations en LSM."),
    ],




    



    [
        'sf' => 'C2-SF3',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Identifier la mise en oeuvre du système d'arme MDCN."),
    ],
    [
        'sf' => 'C2-SF3',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la conduite des systèmes d'armes de son bord en respectant la sécurité et les règles d'engagement."),
    ],

    // An 4
    [
        'sf' => 'C4-SF1',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Adapter le comportement acoustique en fonction de l'environnement et des opérations"),
    ],
    [
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Corréler l'emploi des codes sonar aux réactions d'un sous-marin dans le RETEX de CASEX."),
    ],
    [
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter les réglages sonar à une tâche ordonnée."),
    ],
    [
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Gérer une procédure de traitement d'intrus et une demande d'indentification."),
    ],
    [
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Différencier les principes et messages OPS entre la WSM et PMI."),
    ],
    [
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Conduire les opérations en fonction des zones SM."),
    ],
    [
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Réaliser un briefing NAVSOUM /ZONEX SM."),
    ],
    [
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire l'action opérationnelle en sécurité"),
    ],
    [
        'sf' => 'C7-SF1',
        'competence' => 'C7',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Adapter le type de cible aux téléréglages de la MU 90"),
    ],
    [
        'sf' => 'C7-SF2',
        'competence' => 'C7',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Déterminer le type d'attaque torpille suivant les zones de réactions et les manoeuvre anti-torpille."),
    ],
    [
        'sf' => 'C7-SF2',
        'competence' => 'C7',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Conduire un tir torpille et le lancement de contre-mesures."),
    ],

    // An 5
    [
        'sf' => 'C4-SF4',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Distinguer les différents systèmes de détection (radar, optique, optronique...) et leurs performances pour aider à la classification des contacts."),
    ],
    [
        'sf' => 'C4-SF4',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O2 - Identifier les avantages et inconvénients de la détection multistatique ainsi que la notion de multistatisme subit."),
    ],
    [
        'sf' => 'C4-SF5',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Réaliser le suivi de la signature acoustique de son bâtiment."),
    ],
    [
        'sf' => 'C4-SF5',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Évaluer la situation acoustique de son unité afin de l'adapter en fonction des opérations."),
    ],


    [
        'sf' => 'C4-SF7',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Diriger le module ASM en tant que CASM."),
    ],
   



    
    [
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Adapter le plan EMCON et les mesures ZIPPO suivant la menace."),
    ],
    [
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Gérer la menace en LAA."),
    ],
    
    [
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 -  Gérer la menace et l'engagement en LAN."),
    ],
    [
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Identifier la documentation et systèmes nécessaires à la réalisation des opérations dans tous les domaines de lutte."),
    ],
    

];

// Récupérer les IDs nécessaires
$savoirfaires = DB::table('fcmcentral_savoirfaires')->pluck('id', 'code');
$competences = DB::table('fcmcentral_competences')->pluck('id', 'code');
$competenceSavoirfaires = DB::table('fcmcentral_competence_savoirfaires')->get()->keyBy(function($item) {
    return $item->savoirfaire_id . '-' . $item->competence_id;
});

// On récupère les types de savoir-faire (id => code)
$savoirfaireTypes = DB::table('fcmcentral_savoirfaire_types')->pluck('code', 'id');


$data = [];
$i = 1;

foreach ($array as $item) {
    $sf_id = $savoirfaires[$item['sf']] ?? null;
    $competence_id = $competences[$item['competence']] ?? null;
    $objectif = $item['code'];
    $pivot_id = null;

    if ($sf_id && $competence_id) {
        $key = $sf_id . '-' . $competence_id;
        $code = $item['sf'] . '-' . $item['competence'] . '-' . $objectif; // code lisible
        $pivot = $competenceSavoirfaires[$key] ?? null;
        $pivot_id = $pivot?->id ?? null;

        // Vérifie le type du savoir-faire via la table pivot
        if ($pivot && isset($pivot->savoirfaire_type_id)) {
            $typeCode = $savoirfaireTypes[$pivot->savoirfaire_type_id] ?? null;
            if ($typeCode !== 'C') {
                continue; // On saute si ce n'est pas du compagnonnage
            }
        } else {
            continue; // Pas de pivot ou pas de type, on saute
        }
    }

    if ($pivot_id) {
        $data[] = [
            'uuid' => Str::uuid()->toString(),
            'code' => $code,
            'competence_savoirfaire_id' => $pivot_id,
            'libelle_court' => $item['libelle_court'],
            'libelle_long' => null,
            'coeff' => null,
            'ordre' => $i++,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}



// Insertion en batch
collect($data)->chunk(50)->each(function ($chunk) {
    DB::table('fcmcentral_objectifs')->insertOrIgnore($chunk->toArray());
});

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
$this->command->info('✅ fcmcentral_objectifs: ' . count($data) . ' enregistrements traités');
}
}

