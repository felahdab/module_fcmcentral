<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\QlicksenseExport;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central1');

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Crée un FcmMarin en base sans passer par Eloquent (évite les artefacts mentor_id).
 * Retourne l'ID inséré.
 */
function fcmMarinId(RhMarin $rhMarin): int
{
    return DB::table('fcmcentral_fcm_marins')->insertGetId([
        'rh_marin_id' => $rhMarin->id,
        'data'        => json_encode(['statut_fcm' => 'actif']),
        'cohorte_id'  => null,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
}

/**
 * Crée un marin avec FcmMarin + MarinParcours dans la DB.
 * Retourne les IDs créés pour les assertions.
 */
function creerMarinAvecParcours(
    array $taux = ['global' => 75.00, 'stage' => 60.00, 'activite' => 80.00],
    ?array $parcoursmarin = null
): array {
    $rhMarin = RhMarin::factory()->create([
        'nom'    => 'DUCHMOL',
        'prenom' => 'Alain',
    ]);

    $fcmId = fcmMarinId($rhMarin);

    $parcoursSerialise = ParcoursSerialise::factory()->create([
        'parcours_id' => Parcours::factory()->create()->id,
    ]);

    $mpId = DB::table('fcmcentral_marin_parcours')->insertGetId([
        'fcmmarin_id'         => $fcmId,
        'parcoursserialise_id' => $parcoursSerialise->id,
        'taux_global'         => $taux['global'],
        'taux_stage'          => $taux['stage'],
        'taux_activite'       => $taux['activite'],
        'parcoursmarin'       => $parcoursmarin ? json_encode($parcoursmarin) : null,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return ['rhMarin' => $rhMarin, 'fcmId' => $fcmId, 'mpId' => $mpId];
}

/**
 * Crée un marin FCM SANS MarinParcours (ne doit pas être exporté).
 */
function creerMarinSansParcours(): RhMarin
{
    $rhMarin = RhMarin::factory()->create();
    fcmMarinId($rhMarin);

    return $rhMarin;
}

/**
 * Structure parcoursmarin JSON avec N stages dont $effectues sont validés.
 */
function buildParcoursmarin(int $totalStages, int $effectues): array
{
    $csfs = [];
    for ($i = 0; $i < $totalStages; $i++) {
        $csfs[] = [
            'uuid'             => fake()->uuid(),
            'etat_valid'       => $i < $effectues,
            'etat_propos'      => false,
            'savoirfaire_type' => ['code' => 'S'],
        ];
    }

    return [
        'affectations' => [
            [
                'competences' => [
                    ['competenceSavoirfaires' => $csfs],
                ],
            ],
        ],
    ];
}

/**
 * Crée un TypeEtat en base et retourne son ID.
 */
function creerTypeEtat(string $code): int
{
    return DB::table('fcmcentral_type_etats')->insertGetId([
        'code'          => $code,
        'libelle_court' => $code,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
}

/**
 * Crée un Etat pour un marin RH.
 */
function creerEtat(int $rhMarinId, int $typeEtatId, string $dateDebut): void
{
    DB::table('fcmcentral_etats')->insert([
        'rh_marin_id'  => $rhMarinId,
        'type_etat_id' => $typeEtatId,
        'date_debut'   => $dateDebut,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
}

// ─── Tests ────────────────────────────────────────────────────────────────────

describe('RefreshQlicksenseExportCommand - Général', function () {

    it('retourne EXIT_SUCCESS même sur une base vide', function () {
        $exitCode = Artisan::call('fcmcentral:refresh-qlicksense');

        expect($exitCode)->toBe(0);
    });

    it('ne crée aucune ligne si aucun marin n\'a de parcours', function () {
        creerMarinSansParcours();
        creerMarinSansParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::count())->toBe(0);
    });

    it('tronque la table avant chaque exécution (pas de doublons)', function () {
        creerMarinAvecParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');
        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::count())->toBe(1);
    });

});

describe('RefreshQlicksenseExportCommand - Filtrage marins', function () {

    it('n\'exporte que les marins ayant un parcours', function () {
        creerMarinAvecParcours();   // avec parcours → exporté
        creerMarinSansParcours();   // sans parcours → ignoré

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::count())->toBe(1);
    });

    it('exporte tous les marins ayant un parcours', function () {
        creerMarinAvecParcours();
        creerMarinAvecParcours();
        creerMarinSansParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::count())->toBe(2);
    });

});

describe('RefreshQlicksenseExportCommand - Taux', function () {

    it('reprend taux_global depuis fcmcentral_marin_parcours', function () {
        creerMarinAvecParcours(['global' => 42.50, 'stage' => 30.00, 'activite' => 55.00]);

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect((float) $export->taux_global)->toBe(42.50);
    });

    it('reprend taux_stage depuis fcmcentral_marin_parcours', function () {
        creerMarinAvecParcours(['global' => 50.00, 'stage' => 33.33, 'activite' => 66.67]);

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect((float) $export->taux_stage)->toBe(33.33);
    });

    it('reprend taux_activite depuis fcmcentral_marin_parcours', function () {
        creerMarinAvecParcours(['global' => 50.00, 'stage' => 33.33, 'activite' => 66.67]);

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect((float) $export->taux_activite)->toBe(66.67);
    });

    it('accepte des taux null si la ligne marin_parcours n\'en a pas', function () {
        creerMarinAvecParcours(['global' => null, 'stage' => null, 'activite' => null]);

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect($export->taux_global)->toBeNull()
            ->and($export->taux_stage)->toBeNull()
            ->and($export->taux_activite)->toBeNull();
    });

    it('n\'exporte pas la colonne parcoursmarin dans la table', function () {
        creerMarinAvecParcours(
            parcoursmarin: buildParcoursmarin(3, 1)
        );

        Artisan::call('fcmcentral:refresh-qlicksense');

        // La colonne ne doit plus exister dans le schéma (ou ne pas être dans le modèle)
        $columns = array_keys(DB::table('fcmcentral_qlicksense_exports')->first() ? (array) QlicksenseExport::first() : []);
        expect($columns)->not->toContain('parcoursmarin');
    });

});

describe('RefreshQlicksenseExportCommand - Stages manquants', function () {

    it('calcule 0 stage manquant quand tous sont effectués', function () {
        creerMarinAvecParcours(
            parcoursmarin: buildParcoursmarin(totalStages: 3, effectues: 3)
        );

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->stage_manquants)->toBe(0);
    });

    it('calcule le bon nombre de stages manquants', function () {
        creerMarinAvecParcours(
            parcoursmarin: buildParcoursmarin(totalStages: 5, effectues: 2)
        );

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->stage_manquants)->toBe(3);
    });

    it('calcule 0 stage manquant si parcoursmarin est null', function () {
        creerMarinAvecParcours(parcoursmarin: null);

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->stage_manquants)->toBe(0);
    });

});

describe('RefreshQlicksenseExportCommand - Données marin', function () {

    it('exporte correctement le nom et prénom du marin', function () {
        $rhMarin = RhMarin::factory()->create(['nom' => 'MARTIN', 'prenom' => 'Sophie']);
        $fcmId = fcmMarinId($rhMarin);
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => Parcours::factory()->create()->id]);
        DB::table('fcmcentral_marin_parcours')->insert([
            'fcmmarin_id' => $fcmId, 'parcoursserialise_id' => $ps->id,
            'taux_global' => 50, 'taux_stage' => 50, 'taux_activite' => 50,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect($export->nom)->toBe('MARTIN')
            ->and($export->prenom)->toBe('Sophie');
    });

    it('exporte le marin_uuid', function () {
        ['rhMarin' => $rhMarin] = creerMarinAvecParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');

        $export = QlicksenseExport::first();
        expect($export->marin_uuid)->toBe($rhMarin->uuid);
    });

});

describe('RefreshQlicksenseExportCommand - Etats FCM', function () {

    it('exporte gel_fcm à partir de l\'état FCM_ENP', function () {
        ['rhMarin' => $rhMarin] = creerMarinAvecParcours();

        $typeEtatId = creerTypeEtat('FCM_ENP');
        creerEtat($rhMarin->id, $typeEtatId, '2025-03-15');

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->gel_fcm)->toBe('15/03/2025');
    });

    it('exporte vert_ppa_depuis_le à partir de l\'état FCM_ACT', function () {
        ['rhMarin' => $rhMarin] = creerMarinAvecParcours();

        $typeEtatId = creerTypeEtat('FCM_ACT');
        creerEtat($rhMarin->id, $typeEtatId, '2024-09-01');

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->vert_ppa_depuis_le)->toBe('01/09/2024');
    });

    it('exporte "Non renseigné" si aucun état gel_fcm', function () {
        creerMarinAvecParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->gel_fcm)->toBe('Non renseigné');
    });

    it('exporte "Non renseigné" si aucun état vert_ppa', function () {
        creerMarinAvecParcours();

        Artisan::call('fcmcentral:refresh-qlicksense');

        expect(QlicksenseExport::first()->vert_ppa_depuis_le)->toBe('Non renseigné');
    });

});


