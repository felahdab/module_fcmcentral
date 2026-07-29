<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviCompetencesPage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Models\Marin as RhMarin;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Constantes de test ─────────────────────────────────────────────────────
const COMP_UUID_1 = 'comp1test-1234-5678-comp-11aa11z0c001';
const COMP_UUID_2 = 'comp1test-1234-5678-comp-11aa11z0c002';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function chargerParcoursTestComp(): Parcours
{
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    return Parcours::first();
}

function creerFcmMarinComp(): FcmMarin
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

/**
 * Construit une structure parcoursmarin JSON avec une compétence identifiable,
 * dont les savoir-faire enfants ont chacun un état 'etat_valid' précisé.
 *
 * @param  array<bool>  $etatsCsf  liste des etat_valid pour chaque savoir-faire de la compétence
 */
function buildParcoursMarinAvecCompetence(string $competenceUuid, array $etatsCsf): array
{
    $csfs = [];
    foreach ($etatsCsf as $i => $valide) {
        $csfs[] = [
            'uuid'             => fake()->uuid(),
            'libelle_court'    => "SF{$i}",
            'etat_valid'       => $valide,
            'etat_propos'      => false,
            'savoirfaire_type' => ['code' => 'C', 'libelle_court' => 'Compagnonnage'],
            'savoirfaire'      => ['code' => "SF{$i}", 'libelle_court' => "SF{$i}"],
        ];
    }

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
                        'uuid'                    => $competenceUuid,
                        'code'                    => 'C1',
                        'libelle_court'           => 'Compétence test',
                        'etat_valid'              => false,
                        'etat_propos'             => false,
                        'competenceSavoirfaires'  => $csfs,
                    ],
                ],
            ],
        ],
    ];
}

function creerMarinParcoursAvecCompetence(
    string $competenceUuid,
    array $etatsCsf,
    ?ParcoursSerialise $parcoursSerialise = null
): MarinParcours {
    if (! $parcoursSerialise) {
        $parcours = chargerParcoursTestComp();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
    }

    $fcmMarin = creerFcmMarinComp();

    return MarinParcours::factory()
        ->pourFcmMarin($fcmMarin)
        ->pourParcoursSerialise($parcoursSerialise)
        ->avecParcoursMarinData(buildParcoursMarinAvecCompetence($competenceUuid, $etatsCsf))
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

describe('SuiviCompetencesPage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->assertSuccessful();
    });

    it('affiche l\'onglet competences par défaut', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->assertSet('activeTab', 'competences');
    });

    it('bascule vers l\'onglet marins', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->call('setTab', 'marins')
            ->assertSet('activeTab', 'marins');
    });

});

// ============================================================
// FILTRES EN CASCADE
// ============================================================

describe('SuiviCompetencesPage - Filtres en cascade', function () {

    it('clearCompetenceFilter remet compétence et statut à null', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->call('clearCompetenceFilter')
            ->assertSet('selectedCompetenceUuid', null)
            ->assertSet('selectedStatut', null);
    });

    it('selectCompetenceAndShowMarins bascule sur l\'onglet marins avec la compétence sélectionnée', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->call('selectCompetenceAndShowMarins', COMP_UUID_1)
            ->assertSet('activeTab', 'marins')
            ->assertSet('selectedCompetenceUuid', COMP_UUID_1);
    });

});

// ============================================================
// ONGLET COMPÉTENCES — STATISTIQUES
// ============================================================

describe('SuiviCompetencesPage - Onglet Compétences (stats)', function () {

    it('retourne un tableau vide si aucun parcours sélectionné', function () {
        $component = Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class);

        expect($component->instance()->getStatsParCompetence())->toBeEmpty();
    });

    it('comptabilise correctement effectuées / en cours / non effectuées', function () {
        $parcours = chargerParcoursTestComp();
        $parcoursSerialise = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, true], $parcoursSerialise);   // effectuée (tous valides)
        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, false], $parcoursSerialise);  // en cours (partiel)
        creerMarinParcoursAvecCompetence(COMP_UUID_1, [false, false], $parcoursSerialise); // non effectuée

        $component = Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedParcoursId', $parcours->id);

        $stats = $component->instance()->getStatsParCompetence();
        $stat = collect($stats)->firstWhere('uuid', COMP_UUID_1);

        expect($stat['total'])->toBe(3)
            ->and($stat['effectues'])->toBe(1)
            ->and($stat['en_cours'])->toBe(1)
            ->and($stat['non_effectues'])->toBe(1);
    });

});

// ============================================================
// ONGLET MARINS — TABLE
// ============================================================

describe('SuiviCompetencesPage - Onglet Marins (table)', function () {

    it('filtre les marins par compétence UUID', function () {
        $parcours = chargerParcoursTestComp();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true], $ps);
        creerMarinParcoursAvecCompetence(COMP_UUID_2, [true], $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->assertCountTableRecords(1);
    });

    it('n\'affiche que les marins avec la compétence effectuée (filtre effectue)', function () {
        $parcours = chargerParcoursTestComp();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);

        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, true], $ps);
        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, false], $ps);
        creerMarinParcoursAvecCompetence(COMP_UUID_1, [false, false], $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->set('selectedStatut', 'effectue')
            ->assertCountTableRecords(1);
    });

});

// ============================================================
// CALCUL DU STATUT (computeStatut)
// ============================================================

describe('SuiviCompetencesPage - Calcul statut', function () {

    it('retourne "Effectué" quand tous les savoir-faire sont valides', function () {
        $mp = creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, true]);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Effectué');
    });

    it('retourne "En cours" quand certains savoir-faire sont valides', function () {
        $mp = creerMarinParcoursAvecCompetence(COMP_UUID_1, [true, false]);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('En cours');
    });

    it('retourne "Non effectué" quand aucun savoir-faire n\'est valide', function () {
        $mp = creerMarinParcoursAvecCompetence(COMP_UUID_1, [false, false]);

        $page = Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedCompetenceUuid', COMP_UUID_1)
            ->instance();

        expect($page->computeStatut($mp->fresh()))->toBe('Non effectué');
    });

});

// ============================================================
// EXPORT CSV
// ============================================================

describe('SuiviCompetencesPage - Export CSV', function () {

    it('l\'action exportCsv est présente dans la table', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->assertTableActionExists('exportCsv');
    });

    it('l\'export CSV ne lève pas d\'exception', function () {
        $parcours = chargerParcoursTestComp();
        $ps = ParcoursSerialise::factory()->create(['parcours_id' => $parcours->id]);
        creerMarinParcoursAvecCompetence(COMP_UUID_1, [true], $ps);

        Livewire::actingAs($this->admin)
            ->test(SuiviCompetencesPage::class)
            ->set('selectedParcoursId', $parcours->id)
            ->callTableAction('exportCsv')
            ->assertSuccessful();
    });

});
