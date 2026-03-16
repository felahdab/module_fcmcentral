<?php

namespace Modules\FcmCentral\Database\Seeders\Parcours;

use Illuminate\Database\Seeder;
use Modules\FcmCentral\Models\Domaine;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Savoirfaire;

class DeasmSavoirfairesSeeder extends Seeder
{
    private function cleanString(string $string): string
    {
        return preg_replace('/[^A-Za-z0-9 \-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }

    public function run(): void
    {
        // Chargement des relations indexées par code
        $domaines    = Domaine::pluck('id', 'code');
        $competences = Competence::select('id', 'code', 'color')->get()->keyBy('code');

        $uuids = [
            'C1-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc1sf1',
            'C1-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc1sf2',
            'C1-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc1sf3',
            'C2-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc2sf1',
            'C2-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc2sf2',
            'C2-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc2sf3',
            'C2-SF4' => 'deasm1b1-1234-5678-savf-11aa11zc2sf4',
            'C2-SF5' => 'deasm1b1-1234-5678-savf-11aa11zc2sf5',
            'C3-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc3sf1',
            'C3-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc3sf2',
            'C3-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc3sf3',
            'C3-SF4' => 'deasm1b1-1234-5678-savf-11aa11zc3sf4',
            'C3-SF5' => 'deasm1b1-1234-5678-savf-11aa11zc3sf5',
            'C4-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc4sf1',
            'C4-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc4sf2',
            'C4-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc4sf3',
            'C4-SF4' => 'deasm1b1-1234-5678-savf-11aa11zc4sf4',
            'C4-SF5' => 'deasm1b1-1234-5678-savf-11aa11zc4sf5',
            'C4-SF6' => 'deasm1b1-1234-5678-savf-11aa11zc4sf6',
            'C4-SF7' => 'deasm1b1-1234-5678-savf-11aa11zc4sf7',
            'C5-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc5sf1',
            'C5-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc5sf2',
            'C5-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc5sf3',
            'C6-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc6sf1',
            'C6-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc6sf2',
            'C6-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc6sf3',
            'C6-SF4' => 'deasm1b1-1234-5678-savf-11aa11zc6sf4',
            'C6-SF5' => 'deasm1b1-1234-5678-savf-11aa11zc6sf5',
            'C6-SF6' => 'deasm1b1-1234-5678-savf-11aa11zc6sf6',
            'C7-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc7sf1',
            'C7-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc7sf2',
            'C7-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc7sf3',
            'C8-SF1' => 'deasm1b1-1234-5678-savf-11aa11zc8sf1',
            'C8-SF2' => 'deasm1b1-1234-5678-savf-11aa11zc8sf2',
            'C8-SF3' => 'deasm1b1-1234-5678-savf-11aa11zc8sf3',
            'C8-SF4' => 'deasm1b1-1234-5678-savf-11aa11zc8sf4',
            'C8-SF5' => 'deasm1b1-1234-5678-savf-11aa11zc8sf5',
            'C8-SF6' => 'deasm1b1-1234-5678-savf-11aa11zc8sf6',
            'C8-SF7' => 'deasm1b1-1234-5678-savf-11aa11zc8sf7',
            'C8-SF8' => 'deasm1b1-1234-5678-savf-11aa11zc8sf8',
        ];

        // Chaque entrée utilise 'competence' (code) et 'domaine' (code)
        // Le foreach se charge de résoudre les IDs
        $rows = [
            ['uuid' => $uuids['C1-SF1'], 'code' => 'C1-SF1', 'competence' => 'C1', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C1-SF1 - Assurer ses fonctions de quart BS au CO en interagissant avec les autres modules (AIR, GE, SURF,LSM,ARM)."),        'libelle_long' => $this->cleanString("Assurer ses fonctions de quart BS au CO en interagissant avec les autres modules (AIR, GE, SURF,LSM,ARM).")],
            ['uuid' => $uuids['C1-SF2'], 'code' => 'C1-SF2', 'competence' => 'C1', 'domaine' => 'GEN', 'same' => 'A', 'libelle_court' => $this->cleanString("C1-SF2 - Identifier les informations clés (activités du bâtiment, LOG, SIC, RENS...) du briefing OPS pouvant impacter les opérations de son domaine de lutte."), 'libelle_long' => $this->cleanString("Identifier les informations clés (activités du bâtiment, LOG, SIC, RENS...) du briefing OPS pouvant impacter les opérations de son domaine de lutte.")],
            ['uuid' => $uuids['C1-SF3'], 'code' => 'C1-SF3', 'competence' => 'C1', 'domaine' => 'GEN', 'same' => 'A', 'libelle_court' => $this->cleanString("C1-SF3 - Exploiter les messages opérationnels liés aux différents domaines de lutte."),                                                  'libelle_long' => $this->cleanString("Exploiter les messages opérationnels liés aux différents domaines de lutte.")],
            ['uuid' => $uuids['C2-SF1'], 'code' => 'C2-SF1', 'competence' => 'C2', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C2-SF1 - Conduire les opérations dans les domaines de luttes au-dessus de la surface (LAN, LAA)"),                                    'libelle_long' => $this->cleanString("Conduire les opérations dans les domaines de luttes au-dessus de la surface (LAN, LAA)")],
            ['uuid' => $uuids['C2-SF2'], 'code' => 'C2-SF2', 'competence' => 'C2', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C2-SF2 - Mettre en oeuvre les tâches des différents domaines de luttes suivant le contexte opérationnel (EXERCICES, ESCORT, INTERDICTION DE ZONE, TRANSIT..)"), 'libelle_long' => $this->cleanString("Mettre en oeuvre les tâches des différents domaines de luttes suivant le contexte opérationnel (EXERCICES, ESCORT, INTERDICTION DE ZONE, TRANSIT..)")],
            ['uuid' => $uuids['C2-SF3'], 'code' => 'C2-SF3', 'competence' => 'C2', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C2-SF3 - Adapter la mise en oeuvre des armes à une situation tactique donnée"),                                                          'libelle_long' => $this->cleanString("Adapter la mise en oeuvre des armes à une situation tactique donnée")],
            ['uuid' => $uuids['C2-SF4'], 'code' => 'C2-SF4', 'competence' => 'C2', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C2-SF4 - Corréler la gestion des menaces et des communications dans les domaines de lutte au-dessus de la surface (EMCON, dispositif, emploi des moyens, ZIPPO)"), 'libelle_long' => $this->cleanString("Corréler la gestion des menaces et des communications dans les domaines de lutte au-dessus de la surface (EMCON, dispositif, emploi des moyens, ZIPPO)")],
            ['uuid' => $uuids['C2-SF5'], 'code' => 'C2-SF5', 'competence' => 'C2', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C2-SF5 - Opérer l'action de surface en tant qu'officier de quart opération sur petit bâtiment"),                                     'libelle_long' => $this->cleanString("Opérer l'action de surface en tant qu'officier de quart opération sur petit bâtiment")],
            ['uuid' => $uuids['C3-SF1'], 'code' => 'C3-SF1', 'competence' => 'C3', 'domaine' => 'LSM', 'same' => 'A', 'libelle_court' => $this->cleanString("C3-SF1 - Garantir les règles de sécurité en exercice."),                                                                               'libelle_long' => $this->cleanString("Garantir les règles de sécurité en exercice.")],
            ['uuid' => $uuids['C3-SF2'], 'code' => 'C3-SF2', 'competence' => 'C3', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C3-SF2 - Évaluer l'influence de l'environnement par la réalisation de prédictions de portées sur les performances des senseurs et des systèmes d'armes LSM."), 'libelle_long' => $this->cleanString("Évaluer l'influence de l'environnement par la réalisation de prédictions de portées sur les performances des senseurs et des systèmes d'armes LSM.")],
            ['uuid' => $uuids['C3-SF3'], 'code' => 'C3-SF3', 'competence' => 'C3', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C3-SF3 - Élaborer des éléments buts en bande large et bande étroite en tant que CASM."),                                              'libelle_long' => $this->cleanString("Élaborer des éléments buts en bande large et bande étroite en tant que CASM.")],
            ['uuid' => $uuids['C3-SF4'], 'code' => 'C3-SF4', 'competence' => 'C3', 'domaine' => 'LSM', 'same' => 'A', 'libelle_court' => $this->cleanString("C3-SF4 - Identifier les caractéristiques des différents types de menaces"),                                                            'libelle_long' => $this->cleanString("Identifier les caractéristiques des différents types de menaces")],
            ['uuid' => $uuids['C3-SF5'], 'code' => 'C3-SF5', 'competence' => 'C3', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C3-SF5 - Identifier le référentiel tactique et technique d'un CASM."),                                                               'libelle_long' => $this->cleanString("Identifier le référentiel tactique et technique d'un CASM.")],
            ['uuid' => $uuids['C4-SF1'], 'code' => 'C4-SF1', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'A', 'libelle_court' => $this->cleanString("C4-SF1 - Identifier les tâches ASM (TASM)."),                                                                                       'libelle_long' => $this->cleanString("Identifier les tâches ASM (TASM).")],
            ['uuid' => $uuids['C4-SF2'], 'code' => 'C4-SF2', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF2 - Identifier les opportunités de détection TECHNIQUE (emploi et efficacité des codes sonar) et TACTIQUE (comportement de la menace)"), 'libelle_long' => $this->cleanString("Identifier les opportunités de détection TECHNIQUE (emploi et efficacité des codes sonar) et TACTIQUE (comportement de la menace)")],
            ['uuid' => $uuids['C4-SF3'], 'code' => 'C4-SF3', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF3 - Identifier le référentiel tactique et technique d'un CASM."),                                                               'libelle_long' => $this->cleanString("Identifier le référentiel tactique et technique d'un CASM.")],
            ['uuid' => $uuids['C4-SF4'], 'code' => 'C4-SF4', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF4 - Analyser les éléments issus de la détection multistatique en faisant le lien avec la SITAC."),                             'libelle_long' => $this->cleanString("Analyser les éléments issus de la détection multistatique en faisant le lien avec la SITAC.")],
            ['uuid' => $uuids['C4-SF5'], 'code' => 'C4-SF5', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF5 - Discriminer les sources d'indiscrétion acoustique et les moyens de lutter contre les bruits rayonnés."),                   'libelle_long' => $this->cleanString("Discriminer les sources d'indiscrétion acoustique et les moyens de lutter contre les bruits rayonnés.")],
            ['uuid' => $uuids['C4-SF6'], 'code' => 'C4-SF6', 'competence' => 'C4', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF6 - Élaborer des éléments buts en bande large et bande étroite en tant que CASM."),                                            'libelle_long' => $this->cleanString("Élaborer des éléments buts en bande large et bande étroite en tant que CASM.")],
            ['uuid' => $uuids['C4-SF7'], 'code' => 'C4-SF7', 'competence' => 'C4', 'domaine' => 'ENC', 'same' => 'M', 'libelle_court' => $this->cleanString("C4-SF7 - Garantir les règles de sécurité dans les opérations au-dessus et au-dessous de la surface (SECU SOUM, ENGAGEMENT GABARI DE SECURITE..)"), 'libelle_long' => $this->cleanString("Garantir les règles de sécurité dans les opérations au-dessus et au-dessous de la surface (SECU SOUM, ENGAGEMENT GABARI DE SECURITE..)")],
            ['uuid' => $uuids['C5-SF1'], 'code' => 'C5-SF1', 'competence' => 'C5', 'domaine' => 'GEN', 'same' => 'M', 'libelle_court' => $this->cleanString("C5-SF1 - Gérer l'activité opérationnelle en maitrisant l'information (partie SDC et LDT)"),                                          'libelle_long' => $this->cleanString("Gérer l'activité opérationnelle en maitrisant l'information (partie SDC et LDT)")],
            ['uuid' => $uuids['C5-SF2'], 'code' => 'C5-SF2', 'competence' => 'C5', 'domaine' => 'ANG', 'same' => 'A', 'libelle_court' => $this->cleanString("C5-SF2 - Employer la procédure OTAN dans le cadre de la conduite des opérations"),                                                    'libelle_long' => $this->cleanString("Employer la procédure OTAN dans le cadre de la conduite des opérations")],
            ['uuid' => $uuids['C5-SF3'], 'code' => 'C5-SF3', 'competence' => 'C5', 'domaine' => 'ANG', 'same' => 'A', 'libelle_court' => $this->cleanString("C5-SF3 - Préparer en anglais un exercice combiné de lutte anti-sous-marine (CASEX)."),                                               'libelle_long' => $this->cleanString("Préparer en anglais un exercice combiné de lutte anti-sous-marine (CASEX).")],
            ['uuid' => $uuids['C6-SF1'], 'code' => 'C6-SF1', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF1 - Organiser une logistique efficace au service des activités par la connaissance précise du statut logistique des biens"),    'libelle_long' => $this->cleanString("Organiser une logistique efficace au service des activités par la connaissance précise du statut logistique des biens")],
            ['uuid' => $uuids['C6-SF2'], 'code' => 'C6-SF2', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF2 - Contrôler la conservation des biens et prévenir tout détournement ou dilapidation"),                                        'libelle_long' => $this->cleanString("Contrôler la conservation des biens et prévenir tout détournement ou dilapidation")],
            ['uuid' => $uuids['C6-SF3'], 'code' => 'C6-SF3', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF3 - Distinguer les principes généraux de fonctionnement et de mise en oeuvre des munitions pour leur bon usage."),             'libelle_long' => $this->cleanString("Distinguer les principes généraux de fonctionnement et de mise en oeuvre des munitions pour leur bon usage.")],
            ['uuid' => $uuids['C6-SF4'], 'code' => 'C6-SF4', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF4 - Contrôler la sécurité des règles de stockage et de compatibilité applicables aux munitions et artifices."),                'libelle_long' => $this->cleanString("Contrôler la sécurité des règles de stockage et de compatibilité applicables aux munitions et artifices.")],
            ['uuid' => $uuids['C6-SF5'], 'code' => 'C6-SF5', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF5 - Organiser une logistique efficace au service des activités par la connaissance précise du statut logistique des biens"),    'libelle_long' => $this->cleanString("Organiser une logistique efficace au service des activités par la connaissance précise du statut logistique des biens")],
            ['uuid' => $uuids['C6-SF6'], 'code' => 'C6-SF6', 'competence' => 'C6', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C6-SF6 - Contrôler la conservation des biens et prévenir tout détournement ou dilapidation"),                                        'libelle_long' => $this->cleanString("Contrôler la conservation des biens et prévenir tout détournement ou dilapidation")],
            ['uuid' => $uuids['C7-SF1'], 'code' => 'C7-SF1', 'competence' => 'C7', 'domaine' => 'LSM', 'same' => 'A', 'libelle_court' => $this->cleanString("C7-SF1 - Adapter les réglages torpilles aux differents types de menaces"),                                                            'libelle_long' => $this->cleanString("Adapter les réglages torpilles aux differents types de menaces")],
            ['uuid' => $uuids['C7-SF2'], 'code' => 'C7-SF2', 'competence' => 'C7', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C7-SF2 - Adapter les réglages des IHM selon les situations de tirs d'armes et munitions ASM ou de leurres"),                       'libelle_long' => $this->cleanString("Adapter les réglages des IHM selon les situations de tirs d'armes et munitions ASM ou de leurres")],
            ['uuid' => $uuids['C7-SF3'], 'code' => 'C7-SF3', 'competence' => 'C7', 'domaine' => 'LSM', 'same' => 'M', 'libelle_court' => $this->cleanString("C7-SF3 - Mettre en oeuvre l'action opérationnelle en tant que CASM"),                                                               'libelle_long' => $this->cleanString("Mettre en oeuvre l'action opérationnelle en tant que CASM")],
            ['uuid' => $uuids['C8-SF1'], 'code' => 'C8-SF1', 'competence' => 'C8', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF1 - Planifier les procédures logistiques en vigueur dans la marine dans le cadre de la maintenance préventive ou corrective des systèmes de détection ou de combat"), 'libelle_long' => $this->cleanString("Planifier les procédures logistiques en vigueur dans la marine dans le cadre de la maintenance préventive ou corrective des systèmes de détection ou de combat")],
            ['uuid' => $uuids['C8-SF2'], 'code' => 'C8-SF2', 'competence' => 'C8', 'domaine' => 'SCI', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF2 - Distinguer les différentes règles de base en électricité et en électronique en vue de leur application sur les systèmes de détection ou de combat"), 'libelle_long' => $this->cleanString("Distinguer les différentes règles de base en électricité et en électronique en vue de leur application sur les systèmes de détection ou de combat")],
            ['uuid' => $uuids['C8-SF3'], 'code' => 'C8-SF3', 'competence' => 'C8', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF3 - Panifier les procédures logistiques en vigueur dans la marine dans le cadre de la maintenance préventive ou corrective des systèmes de détection ou de combat"), 'libelle_long' => $this->cleanString("Panifier les procédures logistiques en vigueur dans la marine dans le cadre de la maintenance préventive ou corrective des systèmes de détection ou de combat")],
            ['uuid' => $uuids['C8-SF4'], 'code' => 'C8-SF4', 'competence' => 'C8', 'domaine' => 'LOG', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF4 - Utiliser des outils de résolutions de problème dans le cadre de la maintenance."),                                          'libelle_long' => $this->cleanString("Utiliser des outils de résolutions de problème dans le cadre de la maintenance.")],
            ['uuid' => $uuids['C8-SF5'], 'code' => 'C8-SF5', 'competence' => 'C8', 'domaine' => 'SCI', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF5 - Identifier les notions élémentaires sur la fibre optique et les techniques de nettoyage et d'inspection pour l'entretien et le dépannage du matériel"), 'libelle_long' => $this->cleanString("Identifier les notions élémentaires sur la fibre optique et les techniques de nettoyage et d'inspection pour l'entretien et le dépannage du matériel")],
            ['uuid' => $uuids['C8-SF6'], 'code' => 'C8-SF6', 'competence' => 'C8', 'domaine' => 'SCI', 'same' => 'M', 'libelle_court' => $this->cleanString("C8-SF6 - Différencier les différents types de langages de programmation machine (commandes types Unix)."),                           'libelle_long' => $this->cleanString("Différencier les différents types de langages de programmation machine (commandes types Unix).")],
            ['uuid' => $uuids['C8-SF7'], 'code' => 'C8-SF7', 'competence' => 'C8', 'domaine' => 'SCI', 'same' => 'A', 'libelle_court' => $this->cleanString("C8-SF7 - Identifier l'interconnectivité entre les équipements et les abonnés d'un réseau."),                                        'libelle_long' => $this->cleanString("Identifier l'interconnectivité entre les équipements et les abonnés d'un réseau.")],
            ['uuid' => $uuids['C8-SF8'], 'code' => 'C8-SF8', 'competence' => 'C8', 'domaine' => 'SCI', 'same' => 'A', 'libelle_court' => $this->cleanString("C8-SF8 - Distinguer la contribution des automates dans les fonctionnalités des équipements connectés au réseau informatique"),      'libelle_long' => $this->cleanString("Distinguer la contribution des automates dans les fonctionnalités des équipements connectés au réseau informatique")],
        ];

        $i = 1;

        foreach ($rows as $row) {
            // Résolution des IDs depuis les codes
            $competenceModel = $competences[$row['competence']] ?? null;
            $domaineId       = $domaines[$row['domaine']] ?? null;

            if (! $competenceModel || ! $domaineId) {
                $this->command->warn("⚠️  Competence [{$row['competence']}] ou Domaine [{$row['domaine']}] introuvable — {$row['code']} ignoré.");
                continue;
            }

            Savoirfaire::firstOrCreate(
                ['uuid' => $row['uuid']],
                [
                    'code'          => $row['code'],
                    'libelle_court' => $row['libelle_court'],
                    'libelle_long'  => $row['libelle_long'],
                    'ordre'         => $i++,
                    'same'          => $row['same'],
                    'color'         => $competenceModel->color,
                    'competence_id' => $competenceModel->id,
                    'domaine_id'    => $domaineId,
                ]
            );
        }

        $this->command->info('✅ fcmcentral_savoirfaires: ' . count($rows) . ' enregistrements traités');
    }
}
