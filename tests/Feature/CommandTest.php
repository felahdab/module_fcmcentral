<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\FcmCentral\Models\Parcours;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Fcm Central');

beforeEach(function () {});

it('sauvegarde un parcours sous forme de fichier json', function () {
    expect(Parcours::count())->toBe(0);
    $testfilename = 'Modules/FcmCentral/tests/Feature/parcours_deasm.json';
    Artisan::call("fcmcentral:charge-parcours {$testfilename}");

    $parcours = Parcours::first();
    $filename = 'test-sauvegarde.json';
    Artisan::call("fcmcentral:sauvegarde-parcours --force {$parcours->uuid} {$filename} ");

    $this->assertTrue(File::exists("{$filename}"));

    File::delete($filename);
})->depends('it restaure un parcours depuis un json');

it('restaure un parcours depuis un json', function () {
    expect(Parcours::count())->toBe(0);

    $filename = 'Modules/FcmCentral/tests/Feature/parcours_deasm.json';
    Artisan::call("fcmcentral:charge-parcours {$filename}");

    $this->assertTrue(File::exists("{$filename}"));

    expect(Parcours::count())->toBeGreaterThan(0);
});
