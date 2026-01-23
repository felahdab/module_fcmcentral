<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Models;
use Modules\FcmCentral\Database\Seeders\Parcours\DeasmMasterSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {

});

// ========================================
// TESTS PAGES
// ========================================

it('sauvegarde un parcours sous forme de fichier json', function() {
    $this->seed(DeasmMasterSeeder::class);
    expect(Models\Parcours::count())->toBeGreaterThan(0);
    $parcours = Models\Parcours::first();
    $filename = "test-sauvegarde.json";
    Artisan::call("fcmcentral:sauvegarde-parcours {$parcours->uuid} {$filename}");

    $this->assertTrue(File::exists("$filename"));

});

it('restaure un parcours depuis un json', function() {
    
    $filename = "test-sauvegarde.json";
    Artisan::call("fcmcentral:charge-parcours {$filename}");

    $this->assertTrue(File::exists("$filename"));

    
    expect(Models\Parcours::count())->toBeGreaterThan(0);
    $parcours = Models\Parcours::first();

})->depends('it sauvegarde un parcours sous forme de fichier json');
