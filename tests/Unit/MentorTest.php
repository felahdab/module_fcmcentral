<?php
// filepath: tests/Unit/Models/MentorRelationTest.php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {
    $this->marin = Marin::factory()->create();
    $this->fcmMarin = FcmMarin::factory()->create([
        'rh_marin_id' => $this->marin->id
    ]);
});

// ========================================
// TESTS BASIQUES MENTOR
// ========================================

it('peut savoir si un marin a un mentor', function() {
    // Etant donné - Marin sans mentor
    $this->assertNull($this->fcmMarin->mentor_id);
    $this->assertFalse($this->fcmMarin->hasMentor());
    
    // Quand - Assigner un mentor
    $mentor = Marin::factory()->create();
    $this->fcmMarin->mentor_id = $mentor->id;
    $this->fcmMarin->save();
    
    // Alors
    $this->assertTrue($this->fcmMarin->hasMentor());
    $this->assertEquals($mentor->id, $this->fcmMarin->mentor->id);
});

it('peut savoir si un marin n\'a pas de mentor', function() {
    // Etant donné - Marin créé sans mentor
    $this->assertNull($this->fcmMarin->mentor_id);
    $this->assertFalse($this->fcmMarin->hasMentor());
    
    // Quand - Vérifier explicitement
    $marinsSansMentor = FcmMarin::sansMentor()->get();
    
    // Alors
    $this->assertTrue($marinsSansMentor->contains($this->fcmMarin->id));
    $this->assertNull($this->fcmMarin->mentor);
});

// ========================================
// TESTS RELATIONS MENTOR-MARINS
// ========================================

it('peut savoir si un mentor peut avoir plusieurs marins', function() {
    // Etant donné - Un mentor
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']);
    
    // Quand - Créer plusieurs marins avec le même mentor
    $marin1 = Marin::factory()->create();
    $marin2 = Marin::factory()->create();
    $marin3 = Marin::factory()->create();
    
    $fcmMarin1 = FcmMarin::factory()->create([
        'rh_marin_id' => $marin1->id,
        'mentor_id' => $mentor->id
    ]);
    $fcmMarin2 = FcmMarin::factory()->create([
        'rh_marin_id' => $marin2->id,
        'mentor_id' => $mentor->id
    ]);
    $fcmMarin3 = FcmMarin::factory()->create([
        'rh_marin_id' => $marin3->id,
        'mentor_id' => $mentor->id
    ]);
    
    // Alors - Le mentor a bien plusieurs marins
    $marinsmentores = Marin::mentorePar($mentor)->get();
    $this->assertCount(3, $marinsmentores);
    $this->assertTrue($marinsmentores->contains($marin1->id));
    $this->assertTrue($marinsmentores->contains($marin2->id));
    $this->assertTrue($marinsmentores->contains($marin3->id));
    
    // Vérifier que tous pointent vers le même mentor
    $this->assertEquals($mentor->id, $fcmMarin1->mentor->id);
    $this->assertEquals($mentor->id, $fcmMarin2->mentor->id);
    $this->assertEquals($mentor->id, $fcmMarin3->mentor->id);
});

it('peut savoir si un marin ne peut pas avoir plusieurs mentors (contrainte)', function() {
    // Etant donné - Un marin avec un mentor
    $mentor1 = Marin::factory()->create(['nom' => 'Premier Mentor']);
    $mentor2 = Marin::factory()->create(['nom' => 'Deuxième Mentor']);
    
    $this->fcmMarin->mentor_id = $mentor1->id;
    $this->fcmMarin->save();
    // Rafraichir pour charger la relation
    $this->fcmMarin->refresh();
    $this->fcmMarin->load('mentor');
    
    // Alors - Le marin a bien un seul mentor
    $this->assertEquals($mentor1->id, $this->fcmMarin->mentor_id);
    $this->assertEquals('Premier Mentor', $this->fcmMarin->mentor->nom);
    
    // Quand - Essayer de changer de mentor (remplace l'ancien)
    $this->fcmMarin->mentor_id = $mentor2->id;
    $this->fcmMarin->save();
    // Rafraichir pour charger la relation
    $this->fcmMarin->refresh();
    $this->fcmMarin->load('mentor');
    
    // Alors - Le nouveau mentor remplace l'ancien
    $this->assertEquals($mentor2->id, $this->fcmMarin->mentor_id);
    $this->assertEquals('Deuxième Mentor', $this->fcmMarin->mentor->nom);
    $this->assertNotEquals($mentor1->id, $this->fcmMarin->mentor_id);
    
    // Vérifier qu'il n'y a qu'un seul mentor_id dans la base
    $fcmMarinRefresh = FcmMarin::find($this->fcmMarin->id);
    $this->assertNotNull($fcmMarinRefresh->mentor_id);
    $this->assertEquals($mentor2->id, $fcmMarinRefresh->mentor_id);
});

// ========================================
// TESTS CONTRAINTES MÉTIER
// ========================================

it('peut valider la contrainte unicité mentor par marin', function() {
    // Etant donné
    $mentor1 = Marin::factory()->create();
    $mentor2 = Marin::factory()->create();
    
    // Quand - Un marin a un mentor
    $this->fcmMarin->assignerMentor($mentor1);
    $this->assertTrue($this->fcmMarin->hasMentor());
    $this->assertEquals($mentor1->id, $this->fcmMarin->mentor_id);
    
    // Quand - Changer de mentor
    $this->fcmMarin->assignerMentor($mentor2);
    
    // Alors - Seul le nouveau mentor est présent
    $this->assertEquals($mentor2->id, $this->fcmMarin->mentor_id);
    $this->assertNotEquals($mentor1->id, $this->fcmMarin->mentor_id);
    
    // Vérifier qu'il n'y a qu'un mentor dans la DB
    $this->assertDatabaseHas('fcmcentral_fcm_marins', [
        'id' => $this->fcmMarin->id,
        'mentor_id' => $mentor2->id
    ]);
    
    $this->assertDatabaseMissing('fcmcentral_fcm_marins', [
        'id' => $this->fcmMarin->id,
        'mentor_id' => $mentor1->id
    ]);
});

it('peut retirer un mentor', function() {
    // Etant donné - Marin avec mentor
    $mentor = Marin::factory()->create();
    $this->fcmMarin->assignerMentor($mentor);
    $this->assertTrue($this->fcmMarin->hasMentor());
    
    // Quand - Retirer le mentor
    $this->fcmMarin->retirerMentor();
    
    // Alors
    $this->assertFalse($this->fcmMarin->hasMentor());
    $this->assertNull($this->fcmMarin->mentor_id);
    $this->assertNull($this->fcmMarin->mentor);
});



