<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviObjectifsPage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Constantes de test ─────────────────────────────────────────────────────
const OBJ_UUID_1 = 'obj1test-1234-5678-obje-11aa1c1sf1o1';
const OBJ_UUID_2 = 'obj1test-1234-5678-obje-11aa1c1sf1o2';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function chargerParcoursTestObj(): Parcours
{
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    return Parcours::first();
}

function creerFcmMarinObj(): FcmMarin
{
    $rhMarin = RhMarin::factory()->create();

    $id = DB::table('fcmcentral_fcm_marins')->insertGetId([
        'rh_marin_id' => $rhMarin->id,
        'data'        => json_encode(['statut_fcm' => 'actif']),
        'cohorte_id'  => null,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return FcmMarin::findOrFail($id);
}

function buildParcoursMarinAvecObjectif(string $objectifUuid, bool $effectue = false, bool $propose = false): array
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
                                'uuid'             => fake()->uuid(),
                                'libelle_court'    => 'Savoirfaire test',
                                'etat_valid'       => false,
                                'etat_propos'      => false,
                                'savoirfaire_type' => ['code' => 'C', 'libelle_court' => 'Compagnonnage'],
                                'savoirfaire'      => ['code' => 'SF01', 'libelle_court' => 'Savoirfaire test'],
                                'objectifs' => [
                                    [
                                        'uuid'          => $objectifUuid,
                                        'code'          => 'O1',
                                        'libelle_court' => 'Objectif test',
                                        'etat_valid'    => $effectue,
                                        'etat_propos'   => $propose,
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

function creerMarinParcoursAvecObjectif(
    string $objectifUuid,
    bool $effectue = false,
    bool $propose = false,
    ?ParcoursSerialise $parcoursSerialise = null
): MarinParcours {
    if (! $parcoursSerialise) {
        $parcours = chargerParcoursTestObj();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
    }

    $fcmMarin = creerFcmMarinObj();

    return MarinParcours::factory()
        ->pourFcmMarin($fcmMarin)
        ->pourParcoursSerialise($parcoursSerialise)
        ->avecParcoursMarinData(buildParcoursMarinAvecObjectif($objectifUuid, $effectue, $propose))
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

describe('SuiviObjectifsPage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->assertSuccessful();
    });

    it('affiche l\'onglet objectifs par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->assertSet('activeTab', 'objectifs');
    });

    it('bascule vers l\'onglet marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->call('setTab', 'marins')
            ->assertSet('activeTab', 'marins');
    });

});

// ============================================================
// FILTRES EN CASCADE
// ============================================================

describe('SuiviObjectifsPage - Filtres en cascade', function () {

    it('clearObjectifFilter remet objectif et statut à null', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedObjectifUuid', OBJ_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->call('clearObjectifFilter')
            ->assertSet('selectedObjectifUuid', null)
            ->assertSet('selectedStatut', null);
    });

    it('selectObjectifAndShowMarins bascule sur l\'onglet marins avec l\'objectif sélectionné', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->call('selectObjectifAndShowMarins', OBJ_UUID_1)
            ->assertSet('activeTab', 'marins')
            ->assertSet('selectedObjectifUuid', OBJ_UUID_1);
    });

});

// ============================================================
// ONGLET OBJECTIFS — STATISTIQUES
// ============================================================

describe('SuiviObjectifsPage - Onglet Objectifs (stats)', function () {

    it('retourne un tableau vide si aucun parcours sélectionné', function () {
        $component = Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class);

        expect($component->instance()->getStatsParObjectif())->toBeEmpty();
    });

    it('comptabilise correctement effectués / proposés / non effectués', function () {
        $parcours = chargerParcoursTestObj();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecObjectif(OBJ_UUID_1, true, false, $parcoursSerialise);
        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, true, $parcoursSerialise);
        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, false, $parcoursSerialise);

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParObjectif();
        $stat = collect($stats)->firstWhere('uuid', OBJ_UUID_1);

        expect($stat['total'])->toBe(3)
            ->and($stat['effectues'])->toBe(1)
            ->and($stat['proposes'])->toBe(1)
            ->and($stat['non_effectues'])->toBe(1);
    });

});

// ============================================================
// ONGLET MARINS — TABLE
// ============================================================

describe('SuiviObjectifsPage - Onglet Marins (table)', function () {

    it('filtre les marins par objectif UUID', function () {
        $parcours = chargerParcoursTestObj();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, false, $ps);
        creerMarinParcoursAvecObjectif(OBJ_UUID_2, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedObjectifUuid', OBJ_UUID_1)
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins ayant effectué l\'objectif (filtre effectue)', function () {
        $parcours = chargerParcoursTestObj();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecObjectif(OBJ_UUID_1, true, false, $ps);
        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, true, $ps);
        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedObjectifUuid', OBJ_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->assertCountTableRecords(1);
    });

});

// ============================================================
// CALCUL DU STATUT (computeStatut)
// ============================================================

describe('SuiviObjectifsPage - Calcul statut', function () {

    it('retourne "Effectué" quand etat_valid est true', function () {
        $mp = creerMarinParcoursAvecObjectif(OBJ_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedObjectifUuid', OBJ_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Effectué');
    });

    it('retourne "Proposé" quand etat_propos est true et etat_valid false', function () {
        $mp = creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, true);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedObjectifUuid', OBJ_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Proposé');
    });

    it('retourne un résumé X/N objectifs quand aucun objectif n\'est sélectionné', function () {
        $mp = creerMarinParcoursAvecObjectif(OBJ_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toMatch('/^\d+\/\d+ objectifs$/');
    });

});

// ============================================================
// EXPORT CSV
// ============================================================

describe('SuiviObjectifsPage - Export CSV', function () {

    it('l\'action exportCsv est présente dans la table', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->assertTableActionExists('exportCsv');
    });

    it('l\'export CSV ne lève pas d\'exception', function () {
        $parcours = chargerParcoursTestObj();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecObjectif(OBJ_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviObjectifsPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->callTableAction('exportCsv')
            ->assertSuccessful();
    });

});
