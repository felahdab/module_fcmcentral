<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Fcm Central');

beforeEach(function () {
    $this->marin = Marin::factory()->create();
    $this->fcmMarin = FcmMarin::factory()->create([
        'rh_marin_id' => $this->marin->id,
    ]);
});

// ========================================
// TESTS BASIQUES MENTOR
// ========================================

it('un marin sans mentor : hasMentor() retourne false', function () {
    $this->assertFalse($this->fcmMarin->hasMentor());
    $this->assertCount(0, $this->fcmMarin->mentors);
});

it('un marin avec un mentor : hasMentor() retourne true', function () {
    $mentor = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);

    $this->assertTrue($this->fcmMarin->hasMentor());
    $this->assertCount(1, $this->fcmMarin->fresh()->mentors);
    $this->assertEquals($mentor->id, $this->fcmMarin->fresh()->mentors->first()->id);
});

it('un marin peut avoir plusieurs mentors', function () {
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();
    $mentor3 = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor1);
    $this->fcmMarin->assignerMentor($mentor2);
    $this->fcmMarin->assignerMentor($mentor3);

    $mentors = $this->fcmMarin->fresh()->mentors;
    $this->assertCount(3, $mentors);
    $this->assertTrue($mentors->contains($mentor1->id));
    $this->assertTrue($mentors->contains($mentor2->id));
    $this->assertTrue($mentors->contains($mentor3->id));
});

it('assigner le même mentor deux fois ne crée pas de doublon', function () {
    $mentor = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);
    $this->fcmMarin->assignerMentor($mentor);

    $this->assertCount(1, $this->fcmMarin->fresh()->mentors);
});

// ========================================
// TESTS RETRAIT DE MENTOR
// ========================================

it('peut retirer un mentor spécifique sans toucher aux autres', function () {
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor1);
    $this->fcmMarin->assignerMentor($mentor2);

    $this->fcmMarin->retirerMentor($mentor1);

    $mentors = $this->fcmMarin->fresh()->mentors;
    $this->assertCount(1, $mentors);
    $this->assertFalse($mentors->contains($mentor1->id));
    $this->assertTrue($mentors->contains($mentor2->id));
});

it('peut retirer tous les mentors d\'un coup', function () {
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor1);
    $this->fcmMarin->assignerMentor($mentor2);

    $this->fcmMarin->retirerTousLesMentors();

    $this->assertFalse($this->fcmMarin->hasMentor());
    $this->assertCount(0, $this->fcmMarin->fresh()->mentors);
});

// ========================================
// TESTS SCOPE
// ========================================

it('scope sansMentor ne retourne que les marins sans mentor', function () {
    $mentor = Marin::factory()->create();
    $this->fcmMarin->assignerMentor($mentor);

    $fcmMarinSansMentor = FcmMarin::factory()->create([
        'rh_marin_id' => Marin::factory()->create()->id,
    ]);

    $sansMentor = FcmMarin::sansMentor()->get();

    $this->assertFalse($sansMentor->contains($this->fcmMarin->id));
    $this->assertTrue($sansMentor->contains($fcmMarinSansMentor->id));
});

it('scope avecMentor filtre par mentor spécifique', function () {
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();

    $fcmMarin2 = FcmMarin::factory()->create([
        'rh_marin_id' => Marin::factory()->create()->id,
    ]);

    $this->fcmMarin->assignerMentor($mentor1);
    $fcmMarin2->assignerMentor($mentor2);

    $supervises = FcmMarin::avecMentor($mentor1)->get();

    $this->assertTrue($supervises->contains($this->fcmMarin->id));
    $this->assertFalse($supervises->contains($fcmMarin2->id));
});

// ========================================
// TESTS CÔTÉ MARIN (le mentor lui-même)
// ========================================

it('isMentor() retourne true si le marin supervise au moins un marin', function () {
    $mentor = Marin::factory()->create();
    $this->fcmMarin->assignerMentor($mentor);

    $this->assertTrue($mentor->isMentor());
});

it('isMentor() retourne false si le marin ne supervise personne', function () {
    $mentor = Marin::factory()->create();

    $this->assertFalse($mentor->isMentor());
});

it('nombreMarinsSupervises() compte correctement', function () {
    $mentor = Marin::factory()->create();

    $fcmMarin2 = FcmMarin::factory()->create(['rh_marin_id' => Marin::factory()->create()->id]);
    $fcmMarin3 = FcmMarin::factory()->create(['rh_marin_id' => Marin::factory()->create()->id]);

    $this->fcmMarin->assignerMentor($mentor);
    $fcmMarin2->assignerMentor($mentor);
    $fcmMarin3->assignerMentor($mentor);

    $this->assertEquals(3, $mentor->nombreMarinsSupervises());
});

it('est_mentor_de() retourne true si le marin supervise un marin donné', function () {
    $mentor = Marin::factory()->create();
    $this->fcmMarin->assignerMentor($mentor);

    $this->assertTrue($mentor->est_mentor_de($this->marin));
});

it('est_mentor_de() retourne false pour un marin non supervisé', function () {
    $mentor = Marin::factory()->create();
    $autremarin = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);

    $this->assertFalse($mentor->est_mentor_de($autremarin));
});

it('scope mentor sur Marin ne retourne que les marins qui sont mentors', function () {
    $mentor = Marin::factory()->create();
    $nonMentor = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);

    $mentors = Marin::mentor()->get();

    $this->assertTrue($mentors->contains($mentor->id));
    $this->assertFalse($mentors->contains($nonMentor->id));
});

it('getMentors() retourne la collection des mentors du marin', function () {
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor1);
    $this->fcmMarin->assignerMentor($mentor2);

    $mentors = $this->marin->getMentors();

    $this->assertCount(2, $mentors);
    $this->assertTrue($mentors->contains($mentor1->id));
    $this->assertTrue($mentors->contains($mentor2->id));
});

// ========================================
// TEST TABLE PIVOT
// ========================================

it('la table pivot fcmcentral_fcm_marin_mentors est correctement alimentée', function () {
    $mentor = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);

    $this->assertDatabaseHas('fcmcentral_fcm_marin_mentors', [
        'fcm_marin_id' => $this->fcmMarin->id,
        'marin_id' => $mentor->id,
    ]);
});

it('le retrait supprime bien la ligne pivot', function () {
    $mentor = Marin::factory()->create();

    $this->fcmMarin->assignerMentor($mentor);
    $this->fcmMarin->retirerMentor($mentor);

    $this->assertDatabaseMissing('fcmcentral_fcm_marin_mentors', [
        'fcm_marin_id' => $this->fcmMarin->id,
        'marin_id' => $mentor->id,
    ]);
});


