<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class DeasmSavoirfaireSeeder extends Seeder
{
    /**
     * Seeder généré automatiquement pour fcmcentral_savoirfaires
     * Parcours ID: 104
     * Généré le: {\Carbon\Carbon::now()->format('Y-m-d H:i:s')}
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $data = array (
  0 => 
  array (
    'id' => 1335,
    'uuid' => '87241ed5-69ed-47c6-8091-69db9c158b40',
    'libelle_long' => 'C1-SF1 - Décrire le fonctionnement d’un central opérations',
    'libelle_court' => 'C1-SF1 - Décrire le fonctionnement d’un central opérations',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  1 => 
  array (
    'id' => 1336,
    'uuid' => '1d2b12b6-a654-40e9-bcd4-5a774eefbda5',
    'libelle_long' => 'C1-SF2 - Expliquer le concept des différentes taches en LAN, LAA et LSM',
    'libelle_court' => 'C1-SF2 - Expliquer le concept des différentes taches en LAN, LAA et LSM',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:21',
    'updated_at' => '2025-05-20 08:23:21',
  ),
  2 => 
  array (
    'id' => 1337,
    'uuid' => 'f0258b1f-9794-45be-8337-02af107e5345',
    'libelle_long' => 'C1-SF3 - Décrire les armes en LAS et l’engagement en LAN',
    'libelle_court' => 'C1-SF3 - Décrire les armes en LAS et l’engagement en LAN',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  3 => 
  array (
    'id' => 1366,
    'uuid' => '2657dd9f-aa6c-4957-a452-52629b4cb2fd',
    'libelle_long' => 'C1-SF4 - Exploiter les directives communiquées au briefing OPS',
    'libelle_court' => 'C1-SF4 - Exploiter les directives communiquées au briefing OPS',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  4 => 
  array (
    'id' => 1368,
    'uuid' => 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d',
    'libelle_long' => 'C1-SF5 - Rechercher et exploiter le retex technique et tactique disponible',
    'libelle_court' => 'C1-SF5 - Rechercher et exploiter le retex technique et tactique disponible',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  5 => 
  array (
    'id' => 1342,
    'uuid' => 'a3f1e87b-a6df-4a5c-b752-3eb83bb7d0e2',
    'libelle_long' => 'C9-SF1 - Connaitre la menace sous-marine',
    'libelle_court' => 'C9-SF1 - Connaitre la menace sous-marine',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  6 => 
  array (
    'id' => 1346,
    'uuid' => '1ace6d42-88cf-4272-bd50-e3e4f0c7f55b',
    'libelle_long' => 'C6-SF5 - Acquérir les connaissances techniques nécessaires à la réalisation de prédictions de portée ainsi que l\'étude à différentes échelles de l’impact des facteurs environnementaux sur la LSM',
    'libelle_court' => 'C6-SF5 - Acquérir les connaissances techniques',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  7 => 
  array (
    'id' => 1339,
    'uuid' => '144d6a23-d2cb-4404-a5c7-3604d82ec2e8',
    'libelle_long' => 'C6-SF1 - Veiller à l’exécution de la doctrine LSM dans l’application des taches ASM ordonnées.',
    'libelle_court' => 'C6-SF1 - Veiller à l’exécution de la doctrine LSM',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  8 => 
  array (
    'id' => 1341,
    'uuid' => '7b10e497-6204-4a2c-ad48-ab34b2b55bd8',
    'libelle_long' => 'C6-SF4 - Analyser les éléments issus de la détection multistatique en faisant le lien avec la SITAC.',
    'libelle_court' => 'C6-SF4 - Analyser les éléments issus de la détection multistatique',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  9 => 
  array (
    'id' => 1340,
    'uuid' => '5561b92f-9b78-4b21-8ac5-b8a9530cd414',
    'libelle_long' => 'C6-SF3 - Paramétrer correctement sa session SIC 21 ou SIA',
    'libelle_court' => 'C6-SF3 - Paramétrer correctement sa session SIC 21 ou SIA',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  10 => 
  array (
    'id' => 1345,
    'uuid' => '550f8229-c7a8-4f29-b357-9296220e99d9',
    'libelle_long' => 'C6 -SF2 - Suivre l’évolution de la signature acoustique du bâtiment.',
    'libelle_court' => 'C6 -SF2 - Suivre l’évolution de la signature acoustique du bâtiment',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  11 => 
  array (
    'id' => 1347,
    'uuid' => 'db635f60-a504-415a-9575-d3b2617bb78c',
    'libelle_long' => 'C6-SF6 - Rechercher les opportunités de détection TECH et TAC et les soumettre à l’OLASM.',
    'libelle_court' => 'C6-SF6 - Rechercher les opportunités de détection TECH et TAC et les soumettre à l’OLASM.',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  12 => 
  array (
    'id' => 1374,
    'uuid' => 'e1965d44-f4c6-46ba-aed6-7b35627289ce',
    'libelle_long' => 'C6-SF7 - Conduire l’action opérationnelle en tant que CASM sur FREMM',
    'libelle_court' => 'C6-SF7 - Conduire l’action opérationnelle en tant que CASM sur FREMM',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  13 => 
  array (
    'id' => 1373,
    'uuid' => '025cc57d-bcda-4698-a655-329811ef4f78',
    'libelle_long' => 'C6-SF8 - Élaborer des éléments buts en passif bande large et bande étroite',
    'libelle_court' => 'C6-SF8 - Élaborer des éléments buts en passif BL et BE',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  14 => 
  array (
    'id' => 1371,
    'uuid' => 'ef1fb373-c88b-4c60-88dd-5a5da0403cce',
    'libelle_long' => 'C6-SF9 - Appliquer les concepts de lutte dans les domaines de lutte en dessous de la surface',
    'libelle_court' => 'C6-SF9 - Appliquer les concepts de lutte dans les domaines de lutte en dessous de la surface',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  15 => 
  array (
    'id' => 1343,
    'uuid' => '8e2a3469-799e-43a1-9072-91d88ba9b585',
    'libelle_long' => 'C13-SF1 - Identifier les caractéristiques « physiques » des différents types de menace (SSN, SSK…)',
    'libelle_court' => 'C13-SF1 - Identifier les caractéristiques des différents types de menaces',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  16 => 
  array (
    'id' => 1344,
    'uuid' => '675011c0-29a8-4279-aab9-05f3e45311da',
    'libelle_long' => 'C13-SF2 - Configurer les IHM selon les situations de tirs d’armes et munitions ASM ou de leurres.',
    'libelle_court' => 'C13-SF2 - Configurer les IHM selon les situations de tirs d’armes',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
  17 => 
  array (
    'id' => 1365,
    'uuid' => '263c6a8a-8e92-4597-b469-90ac191c3663',
    'libelle_long' => 'C4-SF1 - Appliquer les concepts de lutte dans les domaines de lutte au-dessus de la surface',
    'libelle_court' => 'C4-SF1 - Appliquer les concepts de lutte LAS',
    'url' => NULL,
    'code_sicomp' => NULL,
    'domaine_id' => NULL,
    'coeff' => '1.00',
    'duree' => '1',
    'an_acquis' => NULL,
    'ordre' => 0,
    'mod_acquis' => 'COMPAGNONNAGE',
    'created_at' => '2025-05-20 08:23:22',
    'updated_at' => '2025-05-20 08:23:22',
  ),
);
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect($data)->chunk(50)->each(function ($chunk) {
            DB::table('fcmcentral_savoirfaires')->insertOrIgnore($chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ fcmcentral_savoirfaires: ' . count($data) . ' enregistrements traités');
    }
}