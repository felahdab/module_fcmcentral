<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviStagesPage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Constantes du fichier parcours de test ────────────────────────────────────
// Ces UUIDs proviennent de Modules/FcmCentral/tests/Feature/parcours_deasm.json
const STAGE_UUID_1 = 'deasm1b1-1234-5678-cosf-11aa1c3c3sf5';
const STAGE_UUID_2 = 'deasm1b1-1234-5678-cosf-11aa1c5c5sf1';

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Charge le parcours DEASM1 depuis le fichier JSON de test.
 * Retourne le Parcours créé (premier en base).
 */
function chargerParcoursTest(): Parcours
{
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    return Parcours::first();
}

/**
 * Crée un FcmMarin via DB::table() pour éviter tout artefact Eloquent lié à mentor_id.
 */
function creerFcmMarin(): FcmMarin
{
    $rhMarin = RhMarin::factory()->create();

    $id = DB::table('fcmcentral_fcm_marins')->insertGetId([
        'rh_marin_id'        => $rhMarin->id,
        'data'               => json_encode(['statut_fcm' => 'actif']),
        'cohorte_id'         => null,
        // Statut « en FCM » requis par SuiviStagesPage::buildMarinsQuery() : sédimentation
        // et FCM renseignées, compétence non renseignée.
        'date_sedimentation' => now()->subYear(),
        'date_fcm'           => now()->subMonths(6),
        'date_competence'    => null,
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    return FcmMarin::findOrFail($id);
}

/**
 * Construit une structure parcoursmarin JSON avec un stage identifiable.
 */
function buildParcoursMarinAvecStage(string $stageUuid, bool $effectue = false, bool $propose = false): array
{
    return [
        'uuid'        => fake()->uuid(),
        'etat_valid'  => false,
        'etat_propos' => false,
        'affectations' => [
            [
                'uuid'        => fake()->uuid(),
                'etat_valid'  => false,
                'etat_propos' => false,
                'competences' => [
                    [
                        'uuid'        => fake()->uuid(),
                        'etat_valid'  => false,
                        'etat_propos' => false,
                        'competenceSavoirfaires' => [
                            [
                                'uuid'             => $stageUuid,
                                'libelle_court'    => 'Stage test',
                                'etat_valid'       => $effectue,
                                'etat_propos'      => $propose,
                                'savoirfaire_type' => ['code' => 'S', 'libelle_court' => 'Stage'],
                                'savoirfaire'      => ['code' => 'ST01', 'libelle_court' => 'Stage test'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

/**
 * Crée un MarinParcours complet avec le JSON d'un stage.
 * Utilise DB::table() pour FcmMarin afin d'éviter les problèmes de schéma.
 */
function creerMarinParcoursAvecStage(
    string $stageUuid,
    bool $effectue = false,
    bool $propose = false,
    ?ParcoursSerialise $parcoursSerialise = null
): MarinParcours {
    if (! $parcoursSerialise) {
        $parcours = chargerParcoursTest();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
    }

    $fcmMarin = creerFcmMarin();

    return MarinParcours::factory()
        ->pourFcmMarin($fcmMarin)
        ->pourParcoursSerialise($parcoursSerialise)
        ->avecParcoursMarinData(buildParcoursMarinAvecStage($stageUuid, $effectue, $propose))
        ->create();
}

// ─────────────────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->admin = 1;
    $this->admin->save();
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
});

// ============================================================
// ACCÈS À LA PAGE
// ============================================================

describe('SuiviStagesPage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertSuccessful();
    });

    it('affiche l\'onglet stages par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertSet('activeTab', 'stages');
    });

    it('bascule vers l\'onglet marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->call('setTab', 'marins')
            ->assertSet('activeTab', 'marins');
    });

    it('bascule vers l\'onglet stages depuis marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->call('setTab', 'marins')
            ->call('setTab', 'stages')
            ->assertSet('activeTab', 'stages');
    });

});

// ============================================================
// FILTRES EN CASCADE
// ============================================================

describe('SuiviStagesPage - Filtres en cascade', function () {

    it('clearStageFilter remet stage et statut à null', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->call('clearStageFilter')
            ->assertSet('selectedStageUuid', null)
            ->assertSet('selectedStatut', null);
    });

    it('selectStageAndShowMarins bascule sur l\'onglet marins avec le stage sélectionné', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->call('selectStageAndShowMarins', STAGE_UUID_1)
            ->assertSet('activeTab', 'marins')
            ->assertSet('selectedStageUuid', STAGE_UUID_1);
    });

    it('selectedParcoursId est null par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertSet('selectedParcoursId', null);
    });

    it('selectedStageUuid est null par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertSet('selectedStageUuid', null);
    });

    it('selectedStatut est null par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertSet('selectedStatut', null);
    });

});

// ============================================================
// ONGLET STAGES — STATISTIQUES
// ============================================================

describe('SuiviStagesPage - Onglet Stages (stats)', function () {

    it('retourne un tableau vide si aucun parcours sélectionné', function () {
        $component = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class);

        expect($component->instance()->getStatsParStage())->toBeEmpty();
    });

    it('retourne les stats d\'un stage avec 1 marin non effectué', function () {
        $parcours = chargerParcoursTest();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $parcoursSerialise);

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParStage();
        $stat = collect($stats)->firstWhere('uuid', STAGE_UUID_1);

        expect($stat)->not->toBeNull()
            ->and($stat['total'])->toBe(1)
            ->and($stat['effectues'])->toBe(0)
            ->and($stat['proposes'])->toBe(0)
            ->and($stat['non_effectues'])->toBe(1);
    });

    it('comptabilise correctement effectués / proposés / non effectués', function () {
        $parcours = chargerParcoursTest();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $parcoursSerialise);   // effectué
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, true, $parcoursSerialise);   // proposé
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $parcoursSerialise);  // non effectué
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $parcoursSerialise);  // non effectué

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParStage();
        $stat = collect($stats)->firstWhere('uuid', STAGE_UUID_1);

        expect($stat['total'])->toBe(4)
            ->and($stat['effectues'])->toBe(1)
            ->and($stat['proposes'])->toBe(1)
            ->and($stat['non_effectues'])->toBe(2);
    });

    it('n\'inclut pas les marins d\'un autre parcours dans les stats', function () {
        $parcoursA = chargerParcoursTest();
        $psA = ParcoursSerialise::factory()->create(['parcours_id' => $parcoursA->id]);

        $parcoursB = Parcours::factory()->create();
        $psB = ParcoursSerialise::factory()->create(['parcours_id' => $parcoursB->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $psA);  // dans A effectué
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $psB); // dans B non effectué

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcoursA->id);

        $stats = $component->instance()->getStatsParStage();
        $stat = collect($stats)->firstWhere('uuid', STAGE_UUID_1);

        expect($stat['total'])->toBe(1)
            ->and($stat['effectues'])->toBe(1);
    });

});

// ============================================================
// ONGLET MARINS — TABLE
// ============================================================

describe('SuiviStagesPage - Onglet Marins (table)', function () {

    it('la table est vide quand aucun parcours n\'est sélectionné', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertCountTableRecords(0);
    });

    it('affiche tous les marins du parcours quand aucun stage n\'est sélectionné', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->assertCountTableRecords(2);
    });

    it('filtre les marins par stage UUID', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_2, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins ayant effectué le stage (filtre effectue)', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, true, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins avec le stage proposé (filtre propose)', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, true, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->set('selectedStatut', 'propose')
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins non effectués (filtre non_effectue)', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecStage(STAGE_UUID_1, true, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, true, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->set('selectedStatut', 'non_effectue')
            ->assertCountTableRecords(2);
    });

    it('le filtre statut sans stage sélectionné ne brise pas la table', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedStatut', 'effectue')
            ->assertSuccessful();
    });

});

// ============================================================
// CALCUL DU STATUT (computeStatut)
// ============================================================

describe('SuiviStagesPage - Calcul statut', function () {

    it('retourne "Effectué" quand etat_valid est true', function () {
        $mp = creerMarinParcoursAvecStage(STAGE_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Effectué');
    });

    it('retourne "Proposé" quand etat_propos est true et etat_valid false', function () {
        $mp = creerMarinParcoursAvecStage(STAGE_UUID_1, false, true);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Proposé');
    });

    it('retourne "Non effectué" quand les deux états sont false', function () {
        $mp = creerMarinParcoursAvecStage(STAGE_UUID_1, false, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedStageUuid', STAGE_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Non effectué');
    });

    it('retourne un résumé X/N stages quand aucun stage n\'est sélectionné', function () {
        $mp = creerMarinParcoursAvecStage(STAGE_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->instance(); // selectedStageUuid = null

        expect($page->computeStatut($mp->fresh()))->toMatch('/^\d+\/\d+ stages$/');
    });

});

// ============================================================
// EXPORT CSV
// ============================================================

describe('SuiviStagesPage - Export CSV', function () {

    it('l\'action exportCsv est présente dans la table', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->assertTableActionExists('exportCsv');
    });

    it('l\'export CSV ne lève pas d\'exception', function () {
        $parcours = chargerParcoursTest();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecStage(STAGE_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviStagesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->callTableAction('exportCsv')
            ->assertSuccessful();
    });

});
