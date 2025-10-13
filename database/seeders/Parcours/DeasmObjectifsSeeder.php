<?php
// filepath: Modules/FcmCentral/Database/Seeders/Parcours/DeasmObjectifsSeeder.php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeasmObjectifsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');


$array = [
    // An 2
    [
        'sf' => 'C1-SF1',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Assurer le quart au central opération en interagissant avec les autres modules du CO.',
    ],
    [
        'sf' => 'C1-SF1',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 -  Distinguer les commandements opérationnels sous lesquels l’unité peut évoluer et l’impact sur la conduite des opérations.',
    ],
    [
        'sf' => 'C1-SF1',
        'annee' => 'A2',
        'code' => 'O3',
        'libelle_court' => 'O3 - Analyser le PIM de l’unité et les réglementations associées aux zones maritimes traversées.',
    ],
    [
        'sf' => 'C1-SF4',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Identifier le contenu spécifique de chaque diapositive (N0 à N7) d’un BFG OPS.',
    ],
    [
        'sf' => 'C1-SF4',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 - Assurer la supervision et la présentation d’un BFG OPS (N0 à N7) devant le bord.',
    ],
    [
        'sf' => 'C1-SF5',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Maîtriser l’écosystème d’une CMI et le traitement des différents messages.',
    ],
    [
        'sf' => 'C1-SF5',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 - Exploiter un OPSGEN, un OPTASK et un FLOORPLAN.',
    ],
    [
        'sf' => 'C6-SF1',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Appliquer les règles de sécurité lors d’un exercice ASM ou de la mise en œuvre d’une arme ASM.',
    ],
    [
        'sf' => 'C6-SF1',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 - Diriger le module ASM lors d’une reprise de vue ou durant la lutte anti sous-marin.',
    ],
    [
        'sf' => 'C6-SF1',
        'annee' => 'A2',
        'code' => 'O3',
        'libelle_court' => 'O3 - Conduire l’action ASM dans le respect de la réglementation ZONEX.',
    ],
    [
        'sf' => 'C6-SF4',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Distinguer les différents aspects de la propagation acoustique ainsi que les facteurs externes ou porteur pouvant l’influencer.',
    ],
    [
        'sf' => 'C6-SF4',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 - Conduire l’ensemble du processus de réalisation de prédictions de portées jusqu’à la diffusion des messages ASM associés.',
    ],
    [
        'sf' => 'C6-SF4',
        'annee' => 'A2',
        'code' => 'O3',
        'libelle_court' => 'O3 - Déduire les éléments tactiques des éléments environnementaux.',
    ],
    
    [
        'sf' => 'C9-SF1',
        'annee' => 'A2',
        'code' => 'O1',
        'libelle_court' => 'O1 - Différencier les principes et messages OPS entre la WSM et PMI.',
    ],
    [
        'sf' => 'C9-SF1',
        'annee' => 'A2',
        'code' => 'O2',
        'libelle_court' => 'O2 - Conduire les opérations en fonction des zones SM.',
    ],
    [
        'sf' => 'C9-SF1',
        'annee' => 'A2',
        'code' => 'O3',
        'libelle_court' => 'O3 - Réaliser un briefing NAVSOUM /ZONEX SM.',
    ],

    // An 3
    [
        'sf' => 'C1-SF2',
        'annee' => 'A3',
        'code' => 'O1',
        'libelle_court' => 'O1 - Employer les moyens AERO suivant les opérations en cours.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A3',
        'code' => 'O2',
        'libelle_court' => 'O2 - Adapter la guerre électronique au différents domaines de luttes.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A3',
        'code' => 'O3',
        'libelle_court' => 'O3 - Conduire les opérations en LAN.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A3',
        'code' => 'O4',
        'libelle_court' => 'O4 - Conduire les opérations en LAA.',
    ],
    [
        'sf' => 'C1-SF3',
        'annee' => 'A3',
        'code' => 'O1',
        'libelle_court' => 'O1 - Identifier la mise en œuvre du système d’arme MDCN.',
    ],
    [
        'sf' => 'C1-SF3',
        'annee' => 'A3',
        'code' => 'O2',
        'libelle_court' => 'O2 - Adapter la conduite des systèmes d’armes de son bord en respectant la sécurité et les règles d’engagement.',
    ],
    [
        'sf' => 'C6-SF3',
        'annee' => 'A3',
        'code' => 'O1',
        'libelle_court' => 'O1 - Élaborer une situation tactique suivant les zones VA, CIEA, SA en LAN.',
    ],
    [
        'sf' => 'C6-SF3',
        'annee' => 'A3',
        'code' => 'O2',
        'libelle_court' => 'O2 - Identifier la documentation et systèmes nécessaires à la réalisation des opérations dans tous les domaines de lutte.',
    ],
    [
        'sf' => 'C6-SF7',
        'annee' => 'A3',
        'code' => 'O1',
        'libelle_court' => 'O1 - Employer les différentes formules de calcul et la reconnaissance des points remarquables nécessaires à l’élaboration des éléments but.',
    ],
    [
        'sf' => 'C6-SF7',
        'annee' => 'A3',
        'code' => 'O2',
        'libelle_court' => 'O2 - Résoudre des ELB.',
    ],
    [
        'sf' => 'C13-SF1',
        'annee' => 'A3',
        'code' => 'O1',
        'libelle_court' => 'O1 - Déterminer les caractéristiques d’un sous-marin et ses actions/réactions face à une menace ATBF.',
    ],

    // An 4
    [
        'sf' => 'C1-SF2',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Employer les systèmes du bord suivant les opérations en cours. ',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A4',
        'code' => 'O2',
        'libelle_court' => 'O2 - Adapter la guerre électronique au différents domaines de luttes.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A4',
        'code' => 'O3',
        'libelle_court' => 'O3 - Conduire les opérations en LAN.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A4',
        'code' => 'O4',
        'libelle_court' => 'O4 - Conduire les opérations en LAA.',
    ],
    [
        'sf' => 'C1-SF2',
        'annee' => 'A4',
        'code' => 'O5',
        'libelle_court' => 'O5 - Conduire les opérations en LSM.',
    ],
    [
        'sf' => 'C4-SF1',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 -  Adapter le plan EMCON et les mesures ZIPPO suivant la menace.',
    ],
    [
        'sf' => 'C4-SF1',
        'annee' => 'A4',
        'code' => 'O2',
        'libelle_court' => 'O2 - Gérer la menace en LAA.',
    ],
    [
        'sf' => 'C4-SF1',
        'annee' => 'A4',
        'code' => 'O3',
        'libelle_court' => 'O3 - Gérer la menace et l’engagement en LAN.',
    ],
    [
        'sf' => 'C6-SF8',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Déduire les notions du concept ASM dans l’idée de manœuvre d\'un CASEX',
    ],
    [
        'sf' => 'C6-SF1',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Adapter le comportement acoustique en fonction de l’environnement et des opérations',
    ],
    [
        'sf' => 'C6-SF2',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Réaliser le suivi de la signature acoustique de son bâtiment.',
    ],
    [
        'sf' => 'C6-SF2',
        'annee' => 'A4',
        'code' => 'O2',
        'libelle_court' => 'O2 - Évaluer la situation acoustique de son unité afin de l’adapter en fonction des opérations.',
    ],
    [
        'sf' => 'C6-SF3',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Distinguer les différents systèmes de détection (radar, optique, optronique…) et leurs performances pour aider à la classification des contacts.',
    ],
    [
        'sf' => 'C6-SF3',
        'annee' => 'A4',
        'code' => 'O2',
        'libelle_court' => 'O2 - Identifier les avantages et inconvénients de la détection multistatique ainsi que la notion de multistatisme subit.',
    ],
    [
        'sf' => 'C6-SF5',
        'annee' => 'A4',
        'code' => 'O1',
        'libelle_court' => 'O1 - Corréler l’emploi des codes sonar aux réactions d’un sous-marin dans le RETEX de CASEX.',
    ],
    [
        'sf' => 'C6-SF5',
        'annee' => 'A4',
        'code' => 'O2',
        'libelle_court' => 'O2 - Adapter les réglages sonar à une tâche ordonnée.',
    ],
    [
        'sf' => 'C6-SF5',
        'annee' => 'A4',
        'code' => 'O3',
        'libelle_court' => 'O3 - Gérer une procédure de traitement d’intrus et une demande d’indentification.',
    ],

    // An 5
    [
        'sf' => 'C1-SF7',
        'annee' => 'A5',
        'code' => 'O1',
        'libelle_court' => 'O1 - Diriger le module ASM en tant que CASM.',
    ],
    [
        'sf' => 'C6-SF6',
        'annee' => 'A5',
        'code' => 'O1',
        'libelle_court' => 'O1 - Conduire l’action opérationnelle en sécurité',
    ],
    [
        'sf' => 'C13-SF1',
        'annee' => 'A5',
        'code' => 'O1',
        'libelle_court' => 'O1 - Adapter le type de cible aux téléréglages de la MU 90',
    ],
    [
        'sf' => 'C13-SF2',
        'annee' => 'A5',
        'code' => 'O1',
        'libelle_court' => 'O1 - Déterminer le type d’attaque torpille suivant les zones de réactions et les manœuvre anti-torpille.',
    ],
    [
        'sf' => 'C13-SF2',
        'annee' => 'A5',
        'code' => 'O2',
        'libelle_court' => 'O2 - Conduire un tir torpille et le lancement de contre-mesures.',
    ],
    

];

// Récupérer les IDs nécessaires
$savoirfaires = DB::table('fcmcentral_savoirfaires')->pluck('id', 'code');
$annees = DB::table('fcmcentral_annees')->pluck('id', 'code');
$anneeSavoirfaires = DB::table('fcmcentral_annee_savoirfaires')->get()->keyBy(function($item) {
    return $item->savoirfaire_id . '-' . $item->annee_id;
});

foreach ($array as $item) {
    $sf_id = $savoirfaires[$item['sf']] ?? null;
    $annee_id = $annees[$item['annee']] ?? null;
    $objectif = $item['code'];
    $pivot_id = null;

    if ($sf_id && $annee_id) {
        $key = $sf_id . '-' . $annee_id;
        $code = $item['sf'] . '-' . $item['annee'] . '-' . $objectif; // code lisible
        $pivot_id = isset($anneeSavoirfaires[$key]) ? $anneeSavoirfaires[$key]->id : null;
    }

    if ($pivot_id) {
        $i=1;
       $data[]=
       [
            'uuid' => Str::uuid()->toString(),
            'code' => $code,
            'annee_savoirfaire_id' => $pivot_id,
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

