<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi\SuiviMentorsPage;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MentorAssignment;
use Modules\RH\Models\Unite;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Crée un couple (mentoré, mentor) dans des unités données, et attribue le mentor.
 * Retourne le FcmMarin du mentoré.
 */
function creerCoupleMentorMentore(?Unite $uniteMarin = null, ?Unite $uniteMentor = null): array
{
    $uniteMarin ??= Unite::factory()->create();
    $uniteMentor ??= $uniteMarin;

    $marin = Marin::factory()->create(['unite_id' => $uniteMarin->id]);
    $mentor = Marin::factory()->create(['unite_id' => $uniteMentor->id]);

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
    $fcmMarin->assignerMentor($mentor);

    return [$fcmMarin->fresh(), $mentor, $marin];
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

describe('SuiviMentorsPage - Accès', function () {

    it('charge la page sans erreur', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertSuccessful();
    });

});

// ============================================================
// AFFICHAGE DE LA TABLE
// ============================================================

describe('SuiviMentorsPage - Table', function () {

    it('affiche une ligne par couple mentor/mentoré', function () {
        creerCoupleMentorMentore();
        creerCoupleMentorMentore();

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertCountTableRecords(2);
    });

    it('affiche plusieurs lignes quand un mentor supervise plusieurs marins', function () {
        $unite = Unite::factory()->create();
        $mentor = Marin::factory()->create(['unite_id' => $unite->id]);

        foreach (range(1, 3) as $i) {
            $marin = Marin::factory()->create(['unite_id' => $unite->id]);
            FcmMarin::factory()->create(['rh_marin_id' => $marin->id])->assignerMentor($mentor);
        }

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertCountTableRecords(3);
    });

    it('n\'affiche rien quand aucun marin n\'a de mentor', function () {
        $marin = Marin::factory()->create();
        FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertCountTableRecords(0);
    });

});

// ============================================================
// RECHERCHE
// ============================================================

describe('SuiviMentorsPage - Recherche', function () {

    it('trouve un couple par le nom du mentor', function () {
        [$fcmMarin, $mentor] = creerCoupleMentorMentore();
        creerCoupleMentorMentore(); // bruit

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->searchTable($mentor->nom)
            ->assertCountTableRecords(1);
    });

    it('trouve un couple par le nom du marin supervisé', function () {
        [$fcmMarin, $mentor, $marin] = creerCoupleMentorMentore();
        creerCoupleMentorMentore(); // bruit

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->searchTable($marin->nom)
            ->assertCountTableRecords(1);
    });

    it('ne renvoie rien pour une recherche sans correspondance', function () {
        creerCoupleMentorMentore();

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->searchTable('AucuneCorrespondanceXYZ')
            ->assertCountTableRecords(0);
    });

});

// ============================================================
// FILTRE DÉCALAGE D'UNITÉ
// ============================================================

describe('SuiviMentorsPage - Filtre décalage d\'unité', function () {

    it('le filtre isole les couples avec des unités différentes', function () {
        $uniteA = Unite::factory()->create();
        $uniteB = Unite::factory()->create();

        creerCoupleMentorMentore($uniteA, $uniteA); // même unité
        creerCoupleMentorMentore($uniteA, $uniteB); // décalage

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->filterTable('decalage_unite')
            ->assertCountTableRecords(1);
    });

    it('sans filtre, tous les couples sont affichés', function () {
        $uniteA = Unite::factory()->create();
        $uniteB = Unite::factory()->create();

        creerCoupleMentorMentore($uniteA, $uniteA);
        creerCoupleMentorMentore($uniteA, $uniteB);

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertCountTableRecords(2);
    });

});

// ============================================================
// ACTION "GÉRER LES MENTORS"
// ============================================================

describe('SuiviMentorsPage - Action gererMentors', function () {

    it('l\'action est présente sur chaque ligne', function () {
        creerCoupleMentorMentore();

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->assertTableActionExists('gererMentors');
    });

    it('modifier les mentors via l\'action met à jour la table pivot', function () {
        [$fcmMarin, , $marin] = creerCoupleMentorMentore();
        $nouveauMentor = Marin::factory()->create(['unite_id' => $marin->unite_id]);

        $ligne = MentorAssignment::where('fcm_marin_id', $fcmMarin->id)->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(SuiviMentorsPage::class)
            ->callTableAction('gererMentors', $ligne, data: ['mentors' => [$nouveauMentor->uuid]])
            ->assertSuccessful();

        expect($fcmMarin->fresh()->mentors->pluck('id'))->toContain($nouveauMentor->id);
    });

});
