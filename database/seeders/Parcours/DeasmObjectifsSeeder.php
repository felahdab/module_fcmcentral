<?php
// filepath: Modules/FcmCentral/Database/Seeders/Parcours/DeasmObjectifsSeeder.php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;



class DeasmObjectifsSeeder extends Seeder
{
   
   // Bug Alpine Livewire dans le parcours, j'enleve les caracateres speciaux et accents  pour les seeders
   private  function cleanString($string) {
    return preg_replace('/[^A-Za-z0-9 \-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
}
   
   
    public function run()
    {
       

$array = [
    // An 2
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Assurer le quart au central opération en interagissant avec les autres modules du CO."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Distinguer les commandements opérationnels sous lesquels l'unité peut évoluer et l'impact sur la conduite des opérations."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
        'sf' => 'C1-SF1',
        'competence' => 'C1',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Analyser le PIM de l'unité et les réglementations associées aux zones maritimes traversées."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf2o1',
        'sf' => 'C1-SF2',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Identifier le contenu spécifique de chaque diapositive (N0 à N7) d'un BFG OPS."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf2o2',
        'sf' => 'C1-SF2',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Assurer la supervision et la présentation d'un BFG OPS (N0 à N7) devant le bord."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf3o1',
        'sf' => 'C1-SF3',
        'competence' => 'C1',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Maîtriser l'écosystème d'une CMI et le traitement des différents messages."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c1sf3o2',
        'sf' => 'C1-SF3',
        'competence' => 'C1',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Exploiter un OPSGEN, un OPTASK et un FLOORPLAN."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf1o1',
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Gérer les règles de sécurité lors d'un exercice ASM ou de la mise en oeuvre d'une arme ASM."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf1o2',
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Diriger le module ASM lors d'une reprise de vue ou durant la lutte anti sous-marin."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf1o3',
        'sf' => 'C3-SF1',
        'competence' => 'C3',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire l'action ASM dans le respect de la réglementation ZONEX."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf2o1',
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Distinguer les différents aspects de la propagation acoustique ainsi que les facteurs externes ou porteur pouvant l'influencer."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf2o2',
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Conduire l'ensemble du processus de réalisation de prédictions de portées jusqu'à la diffusion des messages ASM associés."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf2o3',
        'sf' => 'C3-SF2',
        'competence' => 'C3',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Déduire les éléments tactiques des éléments environnementaux."),
    ],
    
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf3o1',
        'sf' => 'C3-SF3',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Employer les différentes formules de calcul et la reconnaissance des points remarquables nécessaires à l'élaboration des éléments but."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf3o2',
        'sf' => 'C3-SF3',
        'competence' => 'C3',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Résoudre des ELB."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c3sf4o1',
        'sf' => 'C3-SF4',
        'competence' => 'C3',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Déterminer les caractéristiques d'un sous-marin et ses actions/réactions face à une menace ATBF."),
    ],

    // An 3
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf1o1',
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Employer les moyens AERO suivant les opérations en cours."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf1o2',
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la guerre électronique au différents domaines de luttes."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf1o3',
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire les opérations en LAN."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf1o4',
        'sf' => 'C2-SF1',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire les opérations en LAA."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf2o1',
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Employer les systèmes du bord suivant les opérations en cours."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf2o2',
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la guerre électronique au différents domaines de luttes."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf2o3',
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Conduire les opérations en LAN."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf2o4',
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire les opérations en LAA."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf2o5',
        'sf' => 'C2-SF2',
        'competence' => 'C2',
        'code' => 'O5',
        'libelle_court' => $this->cleanString("O5 - Conduire les opérations en LSM."),
    ],




    



    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf3o1',
        'sf' => 'C2-SF3',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Identifier la mise en oeuvre du système d'arme MDCN."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf3o2',
        'sf' => 'C2-SF3',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter la conduite des systèmes d'armes de son bord en respectant la sécurité et les règles d'engagement."),
    ],

    // An 4
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf1o1',
        'sf' => 'C4-SF1',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Adapter le comportement acoustique en fonction de l'environnement et des opérations"),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf2o1',
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Corréler l'emploi des codes sonar aux réactions d'un sous-marin dans le RETEX de CASEX."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf2o2',
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Adapter les réglages sonar à une tâche ordonnée."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf1o3',
        'sf' => 'C4-SF2',
        'competence' => 'C4',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Gérer une procédure de traitement d'intrus et une demande d'indentification."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf3o1',
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Différencier les principes et messages OPS entre la WSM et PMI."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf3o2',
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Conduire les opérations en fonction des zones SM."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf3o3',
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 - Réaliser un briefing NAVSOUM /ZONEX SM."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf3o4',
        'sf' => 'C4-SF3',
        'competence' => 'C4',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Conduire l'action opérationnelle en sécurité"),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c7sf1o1',
        'sf' => 'C7-SF1',
        'competence' => 'C7',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Adapter le type de cible aux téléréglages de la MU 90"),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c7sf2o1',
        'sf' => 'C7-SF2',
        'competence' => 'C7',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Déterminer le type d'attaque torpille suivant les zones de réactions et les manoeuvre anti-torpille."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c7sf2o1',
        'sf' => 'C7-SF2',
        'competence' => 'C7',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Conduire un tir torpille et le lancement de contre-mesures."),
    ],

    // An 5
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf4o1',
        'sf' => 'C4-SF4',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Distinguer les différents systèmes de détection (radar, optique, optronique...) et leurs performances pour aider à la classification des contacts."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf4o2',
        'sf' => 'C4-SF4',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Identifier les avantages et inconvénients de la détection multistatique ainsi que la notion de multistatisme subit."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf5o1',
        'sf' => 'C4-SF5',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Réaliser le suivi de la signature acoustique de son bâtiment."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf5o2',
        'sf' => 'C4-SF5',
        'competence' => 'C4',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 - Évaluer la situation acoustique de son unité afin de l'adapter en fonction des opérations."),
    ],


    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c4sf7o1',
        'sf' => 'C4-SF7',
        'competence' => 'C4',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 - Diriger le module ASM en tant que CASM."),
    ],
   



    
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf4o1',
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O1',
        'libelle_court' => $this->cleanString("O1 -  Adapter le plan EMCON et les mesures ZIPPO suivant la menace."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf4o2',
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O2',
        'libelle_court' => $this->cleanString("O2 -  Gérer la menace en LAA."),
    ],
    
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf4o3',
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O3',
        'libelle_court' => $this->cleanString("O3 -  Gérer la menace et l'engagement en LAN."),
    ],
    [
        'uuid' => 'deasm1b1-1234-5678-obje-11aa1c2sf4o4',
        'sf' => 'C2-SF4',
        'competence' => 'C2',
        'code' => 'O4',
        'libelle_court' => $this->cleanString("O4 - Identifier la documentation et systèmes nécessaires à la réalisation des opérations dans tous les domaines de lutte."),
    ],
    

];

    // Pré-chargement des relations
    $savoirfaires = Savoirfaire::pluck('id', 'code');
    $competences = Competence::pluck('id', 'code');
    $savoirfaireTypeC = SavoirfaireType::where('code', 'C')->value('id');
    
    // Pivot indexé par "savoirfaire_id-competence_id"
    $pivots = CompetenceSavoirfaire::all()->keyBy(fn($p) => $p->savoirfaire_id . '-' . $p->competence_id);
    
    $i = 1;
    $count = 0;
    
    foreach ($array as $item) {
    $sfId = $savoirfaires[$item['sf']] ?? null;
    $competenceId = $competences[$item['competence']] ?? null;
    
    if (! $sfId || ! $competenceId) {
    $this->command->warn("⚠️ SF [{$item['sf']}] ou Compétence [{$item['competence']}] introuvable — ignoré.");
    continue;
    }
    
    $pivotKey = $sfId . '-' . $competenceId;
    $pivot = $pivots[$pivotKey] ?? null;
    
    // On ne traite que les savoir-faire de type Compagnonnage (code = 'C')
    if (! $pivot || ($pivot->savoirfaire_type_id !== $savoirfaireTypeC)) {
    continue;
    }
    
    $code = $item['sf'] . '-' . $item['competence'] . '-' . $item['code'];
    
    Objectif::firstOrCreate(
    ['uuid' => $item['uuid']],
    [
    'code' => $code,
    'competence_savoirfaire_id' => $pivot->id,
    'libelle_court' => $item['libelle_court'],
    'libelle_long' => null,
    'coeff' => null,
    'ordre' => $i++,
    ]
    );
    
    $count++;
    }
    
    $this->command->info("✅ fcmcentral_objectifs: {$count} enregistrements traités");
    }

}

