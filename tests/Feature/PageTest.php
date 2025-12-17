<?php

use function Pest\Livewire\livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Filament\Facades\Filament;
use App\Models\User;
use Filament\Pages\Dashboard;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Models\Marin;
use function Pest\Laravel\{actingAs};
use Livewire\Livewire;


uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('FCM Central'),
    );

    $this->admin = User::factory()->create();
    $this->admin->admin = 1;
    $this->admin->save();
});

it('affiche le panneau principal FCM Central', function() {
    Livewire::actingAs($this->admin)
    ->test(Dashboard::class)
        ->assertSee('Tableau de bord');
});

it('affiche la page liste des compétences', function() {
    $this->actingAs($this->admin)
        ->get(CompetenceResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de compétence', function() {
    $this->actingAs($this->admin)
        ->get(CompetenceResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des marins', function() {
    $this->actingAs($this->admin)
        ->get(MarinResource::getUrl('index'))
        ->assertSuccessful();
});


