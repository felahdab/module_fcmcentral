<?php

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Marin;
use Modules\RH\Models\Unite;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central1');

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Crée un utilisateur habilité à recevoir les alertes de décalage d'unité mentor.
 */
function creerDestinataireSuiviMentor(): User
{
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate([
        'name' => 'fcmcentral::suivi.mentor',
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo($permission);

    return $user;
}

/**
 * Crée un marin suivi en FCM (data.fcm.en_fcm = true) dans une unité donnée.
 */
function creerMarinEnFcm(Unite $unite): Marin
{
    return Marin::factory()->create([
        'unite_id' => $unite->id,
        'data' => ['fcm' => ['en_fcm' => true]],
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->destinataire = creerDestinataireSuiviMentor();
});

// ============================================================
// PAS DE CHANGEMENT D'UNITÉ → RIEN NE SE PASSE
// ============================================================

it('ne notifie rien quand l\'unité ne change pas', function () {
    $unite = Unite::factory()->create();
    $marin = creerMarinEnFcm($unite);

    $marin->nom = 'AutreNom';
    $marin->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(0);
});

// ============================================================
// MARIN MENTORÉ QUI CHANGE D'UNITÉ
// ============================================================

it('notifie quand un mentoré change d\'unité et diverge de son mentor', function () {
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $marin = creerMarinEnFcm($uniteA);
    $mentor = Marin::factory()->create(['unite_id' => $uniteA->id]);

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
    $fcmMarin->assignerMentor($mentor);

    // Le marin part dans l'unité B : décalage avec son mentor resté en A.
    $marin->unite_id = $uniteB->id;
    $marin->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(1);
});

it('ne notifie pas si le mentoré change d\'unité mais reste aligné avec son mentor', function () {
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $marin = creerMarinEnFcm($uniteA);
    $mentor = Marin::factory()->create(['unite_id' => $uniteB->id]);

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
    $fcmMarin->assignerMentor($mentor);

    // Le marin rejoint la même unité que son mentor : plus de décalage.
    $marin->unite_id = $uniteB->id;
    $marin->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(0);
});

// ============================================================
// MARIN MENTOR QUI CHANGE D'UNITÉ
// ============================================================

it('notifie quand un mentor change d\'unité et diverge d\'un de ses mentorés', function () {
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $mentor = creerMarinEnFcm($uniteA);
    $mentore = Marin::factory()->create(['unite_id' => $uniteA->id]);

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $mentore->id]);
    $fcmMarin->assignerMentor($mentor);

    // Le mentor part dans l'unité B : décalage avec son mentoré resté en A.
    $mentor->unite_id = $uniteB->id;
    $mentor->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(1);
});

it('notifie une seule fois même si plusieurs mentorés divergent', function () {
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $mentor = creerMarinEnFcm($uniteA);

    foreach (range(1, 3) as $i) {
        $mentore = Marin::factory()->create(['unite_id' => $uniteA->id]);
        $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $mentore->id]);
        $fcmMarin->assignerMentor($mentor);
    }

    $mentor->unite_id = $uniteB->id;
    $mentor->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(1);
});

// ============================================================
// LE MENTOR N'A PAS BESOIN DE SA PROPRE FICHE FCM
// ============================================================

it('détecte un décalage même si le mentor n\'a pas lui-même de fiche FCM (complementsFcm)', function () {
    // Un mentor est un marin RH « ordinaire » : il n'a pas forcément de ligne
    // fcmcentral_fcm_marins pour lui-même, seul le mentoré en a une.
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $marin = creerMarinEnFcm($uniteA);
    $mentor = Marin::factory()->create(['unite_id' => $uniteA->id]); // pas de FcmMarin pour le mentor

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
    $fcmMarin->assignerMentor($mentor);

    expect($mentor->complementsFcm)->toBeNull();

    $marin->unite_id = $uniteB->id;
    $marin->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(1);
});

// ============================================================
// MARIN SANS MENTOR NI MENTORÉ → PAS DE NOTIFICATION
// ============================================================

it('ne notifie pas un marin sans mentor et qui n\'est mentor de personne', function () {
    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $marin = creerMarinEnFcm($uniteA);

    $marin->unite_id = $uniteB->id;
    $marin->save();

    expect($this->destinataire->fresh()->notifications()->count())->toBe(0);
});

// ============================================================
// AUCUN DESTINATAIRE HABILITÉ → PAS D'ERREUR
// ============================================================

it('ne lève pas d\'erreur si personne n\'a la permission suivi.mentor', function () {
    $this->destinataire->revokePermissionTo('fcmcentral::suivi.mentor');

    $uniteA = Unite::factory()->create();
    $uniteB = Unite::factory()->create();

    $marin = creerMarinEnFcm($uniteA);
    $mentor = Marin::factory()->create(['unite_id' => $uniteA->id]);

    $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
    $fcmMarin->assignerMentor($mentor);

    $marin->unite_id = $uniteB->id;

    expect(fn () => $marin->save())->not->toThrow(Exception::class);
});
