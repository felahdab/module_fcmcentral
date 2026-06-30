<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('FcmCentralMigration');

// Ces tests vérifient l'état réel de la base (testmysql), pas la base de test.
// Ils utilisent la connexion 'skeletordb' dont les credentials sont hardcodés dans database.php.

it('la table pivot fcmcentral_fcm_marin_mentors existe', function () {
    $exists = DB::connection('testmysql')->getSchemaBuilder()->hasTable('fcmcentral_fcm_marin_mentors');
    expect($exists)->toBeTrue();
});

it('mentor_id ne existe plus dans fcmcentral_fcm_marins', function () {
    $columns = DB::connection('testmysql')->getSchemaBuilder()->getColumnListing('fcmcentral_fcm_marins');
    expect($columns)->not->toContain('mentor_id');
});

it('le nombre de mentors dans la pivot correspond aux stored_events', function () {
    $db = DB::connection('testmysql');

    $nbDansEvents = $db->table('fcmcentral_stored_events')
        ->where('event_type', 'attribuer_mentor_marin')
        ->distinct('object_uuid')
        ->count('object_uuid');

    $nbDansPivot = $db->table('fcmcentral_fcm_marin_mentors')
        ->distinct('fcm_marin_id')
        ->count('fcm_marin_id');

    expect($nbDansPivot)->toBe($nbDansEvents, "Attendu {$nbDansEvents} marins avec mentor, trouvé {$nbDansPivot} dans la pivot");
});

it('chaque ligne de la pivot pointe vers un fcm_marin existant', function () {
    $db = DB::connection('testmysql');

    $invalides = $db->table('fcmcentral_fcm_marin_mentors')
        ->whereNotIn('fcm_marin_id', $db->table('fcmcentral_fcm_marins')->pluck('id'))
        ->count();

    expect($invalides)->toBe(0);
});
