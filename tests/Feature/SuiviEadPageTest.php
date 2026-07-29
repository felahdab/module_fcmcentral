<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviEadPage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Constantes de test ─────────────────────────────────────────────────────
const EAD_UUID_1 = 'ead1test-1234-5678-cosf-11aa1c1c1ea1';
const EAD_UUID_2 = 'ead1test-1234-5678-cosf-11aa1c1c1ea2';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function chargerParcoursTestEad(): Parcours
{
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    return Parcours::first();
}

function creerFcmMarinEad(): FcmMarin
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

function buildParcoursMarinAvecEad(string $eadUuid, bool $effectue = false, bool $propose = false): array
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
                                'uuid'             => $eadUuid,
                                'libelle_court'    => 'EAD test',
                                'etat_valid'       => $effectue,
                                'etat_propos'      => $propose,
                                'savoirfaire_type' => ['code' => 'EAD', 'libelle_court' => 'Enseignement à distance'],
                                'savoirfaire'      => ['code' => 'EAD01', 'libelle_court' => 'EAD test'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function creerMarinParcoursAvecEad(
    string $eadUuid,
    bool $effectue = false,
    bool $propose = false,
    ?ParcoursSerialise $parcoursSerialise = null
): MarinParcours {
    if (! $parcoursSerialise) {
        $parcours = chargerParcoursTestEad();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
    }

    $fcmMarin = creerFcmMarinEad();

    return MarinParcours::factory()
        ->pourFcmMarin($fcmMarin)
        ->pourParcoursSerialise($parcoursSerialise)
        ->avecParcoursMarinData(buildParcoursMarinAvecEad($eadUuid, $effectue, $propose))
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

describe('SuiviEadPage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->assertSuccessful();
    });

    it('affiche l\'onglet ead par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->assertSet('activeTab', 'ead');
    });

    it('bascule vers l\'onglet marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->call('setTab', 'marins')
            ->assertSet('activeTab', 'marins');
    });

});

// ============================================================
// FILTRES EN CASCADE
// ============================================================

describe('SuiviEadPage - Filtres en cascade', function () {

    it('clearEadFilter remet ead et statut à null', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedEadUuid', EAD_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->call('clearEadFilter')
            ->assertSet('selectedEadUuid', null)
            ->assertSet('selectedStatut', null);
    });

    it('selectEadAndShowMarins bascule sur l\'onglet marins avec l\'EAD sélectionné', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->call('selectEadAndShowMarins', EAD_UUID_1)
            ->assertSet('activeTab', 'marins')
            ->assertSet('selectedEadUuid', EAD_UUID_1);
    });

    it('selectedLieuId est null par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->assertSet('selectedLieuId', null);
    });

});

// ============================================================
// ONGLET EAD — STATISTIQUES
// ============================================================

describe('SuiviEadPage - Onglet EAD (stats)', function () {

    it('retourne un tableau vide si aucun parcours sélectionné', function () {
        $component = Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class);

        expect($component->instance()->getStatsParEad())->toBeEmpty();
    });

    it('comptabilise correctement effectués / proposés / non effectués', function () {
        $parcours = chargerParcoursTestEad();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecEad(EAD_UUID_1, true, false, $parcoursSerialise);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, true, $parcoursSerialise);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $parcoursSerialise);

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParEad();
        $stat = collect($stats)->firstWhere('uuid', EAD_UUID_1);

        expect($stat['total'])->toBe(3)
            ->and($stat['effectues'])->toBe(1)
            ->and($stat['proposes'])->toBe(1)
            ->and($stat['non_effectues'])->toBe(1);
    });

});

// ============================================================
// ONGLET MARINS — TABLE
// ============================================================

describe('SuiviEadPage - Onglet Marins (table)', function () {

    it('la table est vide quand aucun parcours n\'est sélectionné', function () {
        $parcours = chargerParcoursTestEad();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->assertCountTableRecords(0);
    });

    it('filtre les marins par EAD UUID', function () {
        $parcours = chargerParcoursTestEad();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $ps);
        creerMarinParcoursAvecEad(EAD_UUID_2, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedEadUuid', EAD_UUID_1)
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins non effectués (filtre non_effectue)', function () {
        $parcours = chargerParcoursTestEad();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecEad(EAD_UUID_1, true, false, $ps);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $ps);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedEadUuid', EAD_UUID_1)
            ->set('selectedStatut', 'non_effectue')
            ->assertCountTableRecords(2);
    });

});

// ============================================================
// CALCUL DU STATUT (computeStatut)
// ============================================================

describe('SuiviEadPage - Calcul statut', function () {

    it('retourne "Effectué" quand etat_valid est true', function () {
        $mp = creerMarinParcoursAvecEad(EAD_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedEadUuid', EAD_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Effectué');
    });

    it('retourne un résumé X/N EAD quand aucun EAD n\'est sélectionné', function () {
        $mp = creerMarinParcoursAvecEad(EAD_UUID_1, true, false);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toMatch('/^\d+\/\d+ EAD$/');
    });

});

// ============================================================
// EXPORT CSV
// ============================================================

describe('SuiviEadPage - Export CSV', function () {

    it('l\'action exportCsv est présente dans la table', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->assertTableActionExists('exportCsv');
    });

    it('l\'export CSV ne lève pas d\'exception', function () {
        $parcours = chargerParcoursTestEad();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecEad(EAD_UUID_1, false, false, $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviEadPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->callTableAction('exportCsv')
            ->assertSuccessful();
    });

});
