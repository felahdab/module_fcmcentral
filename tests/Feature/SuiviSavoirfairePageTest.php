<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviSavoirfairePage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Constantes de test ─────────────────────────────────────────────────────
const SF_UUID_1 = 'sf1test-1234-5678-cosf-11aa1c1c1sf1';
const SF_UUID_2 = 'sf1test-1234-5678-cosf-11aa1c1c1sf2';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function chargerParcoursTestSf(): Parcours
{
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    return Parcours::first();
}

function creerFcmMarinSf(): FcmMarin
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

function buildParcoursMarinAvecSavoirfaire(string $sfUuid, bool $effectue = false, bool $propose = false): array
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
                                'uuid'             => $sfUuid,
                                'libelle_court'    => 'Savoirfaire test',
                                'etat_valid'       => $effectue,
                                'etat_propos'      => $propose,
                                'savoirfaire_type' => ['code' => 'C', 'libelle_court' => 'Compagnonnage'],
                                'savoirfaire'      => ['code' => 'SF01', 'libelle_court' => 'Savoirfaire test'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function creerMarinParcoursAvecSavoirfaire(
    string $sfUuid,
    bool $effectue = false,
    bool $propose = false,
    ?ParcoursSerialise $parcoursSerialise = null
): MarinParcours {
    if (! $parcoursSerialise) {
        $parcours = chargerParcoursTestSf();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
    }

    $fcmMarin = creerFcmMarinSf();

    return MarinParcours::factory()
        ->pourFcmMarin($fcmMarin)
        ->pourParcoursSerialise($parcoursSerialise)
        ->avecParcoursMarinData(buildParcoursMarinAvecSavoirfaire($sfUuid, $effectue, $propose))
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

describe('SuiviSavoirfairePage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->assertSuccessful();
    });

    it('affiche l\'onglet savoirfaire par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->assertSet('activeTab', 'savoirfaire');
    });

    it('bascule vers l\'onglet marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->call('setTab', 'marins')
            ->assertSet('activeTab', 'marins');
    });

});

// ============================================================
// FILTRES EN CASCADE
// ============================================================

describe('SuiviSavoirfairePage - Filtres en cascade', function () {

    it('clearSavoirfaireFilter remet savoirfaire et statut à null', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedSavoirfaireUuid', SF_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->call('clearSavoirfaireFilter')
            ->assertSet('selectedSavoirfaireUuid', null)
            ->assertSet('selectedStatut', null);
    });

    it('selectSavoirfaireAndShowMarins bascule sur l\'onglet marins avec le savoirfaire sélectionné', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->call('selectSavoirfaireAndShowMarins', SF_UUID_1)
            ->assertSet('activeTab', 'marins')
            ->assertSet('selectedSavoirfaireUuid', SF_UUID_1);
    });

});

// ============================================================
// ONGLET SAVOIRFAIRE — STATISTIQUES
// ============================================================

describe('SuiviSavoirfairePage - Onglet Savoirfaire (stats)', function () {

    it('retourne un tableau vide si aucun parcours sélectionné', function () {
        $component = Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class);

        expect($component->instance()->getStatsParSavoirfaire())->toBeEmpty();
    });

    it('comptabilise correctement effectués / proposés / non effectués', function () {
        $parcours = chargerParcoursTestSf();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, true, false, $parcoursSerialise);
        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, false, true, $parcoursSerialise);
        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, false, false, $parcoursSerialise);

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParSavoirfaire();
        $stat = collect($stats)->firstWhere('uuid', SF_UUID_1);

        expect($stat['total'])->toBe(3)
            ->and($stat['effectues'])->toBe(1)
            ->and($stat['proposes'])->toBe(1)
            ->and($stat['non_effectues'])->toBe(1);
    });

});

// ============================================================
// ONGLET MARINS — TABLE
// ============================================================

describe('SuiviSavoirfairePage - Onglet Marins (table)', function () {

    it('filtre les marins par savoirfaire UUID', function () {
        $parcours = chargerParcoursTestSf();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, false, false, $ps);
        creerMarinParcoursAvecSavoirfaire(SF_UUID_2, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedSavoirfaireUuid', SF_UUID_1)
            ->assertCountTableRecords(1);
    });

    it('affiche tous les marins du parcours quand aucun savoirfaire n\'est sélectionné', function () {
        $parcours = chargerParcoursTestSf();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, false, false, $ps);
        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, true, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->assertCountTableRecords(2);
    });

});

// ============================================================
// CALCUL DU STATUT (computeStatut)
// ============================================================

describe('SuiviSavoirfairePage - Calcul statut', function () {

    it('retourne "Effectué" quand etat_valid est true', function () {
        $mp = creerMarinParcoursAvecSavoirfaire(SF_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedSavoirfaireUuid', SF_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Effectué');
    });

    it('retourne un résumé X/N savoirfaire quand aucun savoirfaire n\'est sélectionné', function () {
        $mp = creerMarinParcoursAvecSavoirfaire(SF_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toMatch('/^\d+\/\d+ savoirfaire$/');
    });

});

// ============================================================
// EXPORT CSV
// ============================================================

describe('SuiviSavoirfairePage - Export CSV', function () {

    it('l\'action exportCsv est présente dans la table', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->assertTableActionExists('exportCsv');
    });

    it('l\'export CSV ne lève pas d\'exception', function () {
        $parcours = chargerParcoursTestSf();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecSavoirfaire(SF_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviSavoirfairePage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->callTableAction('exportCsv')
            ->assertSuccessful();
    });

});
