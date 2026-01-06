<?php

use function Pest\Livewire\livewire;
use function Pest\Laravel\{actingAs};

use Illuminate\Foundation\Testing\RefreshDatabase;

use Livewire\Livewire;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;

use App\Models\User;

use Modules\FcmCentral\Filament\Fcmcentral\Resources;

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
        ->get(Resources\CompetenceResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de compétence', function() {
    $this->actingAs($this->admin)
        ->get(Resources\CompetenceResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des marins', function() {
    $this->actingAs($this->admin)
        ->get(Resources\MarinResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page liste des savoir faire', function() {
    $this->actingAs($this->admin)
        ->get(Resources\SavoirFaireResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de savoir faire', function() {
    $this->actingAs($this->admin)
        ->get(Resources\SavoirFaireResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des domaines', function() {
    $this->actingAs($this->admin)
        ->get(Resources\DomaineResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de domaines', function() {
    $this->actingAs($this->admin)
        ->get(Resources\DomaineResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours type', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ParcoursTypeResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de parcours type', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ParcoursTypeResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des cohortes', function() {
    $this->actingAs($this->admin)
        ->get(Resources\CohorteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de cohorte', function() {
    $this->actingAs($this->admin)
        ->get(Resources\CohorteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des notes', function() {
    $this->actingAs($this->admin)
        ->get(Resources\NoteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de note', function() {
    $this->actingAs($this->admin)
        ->get(Resources\NoteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des types de notes', function() {
    $this->actingAs($this->admin)
        ->get(Resources\TypeNoteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de type de note', function() {
    $this->actingAs($this->admin)
        ->get(Resources\TypeNoteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des etats', function() {
    $this->actingAs($this->admin)
        ->get(Resources\EtatResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de etat', function() {
    $this->actingAs($this->admin)
        ->get(Resources\EtatResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des types etats', function() {
    $this->actingAs($this->admin)
        ->get(Resources\TypeEtatResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de type etat', function() {
    $this->actingAs($this->admin)
        ->get(Resources\TypeEtatResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours figes', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ParcoursSerialiseResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ParcoursResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de parcours', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ParcoursResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des affectations', function() {
    $this->actingAs($this->admin)
        ->get(Resources\AffectationResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de affectations', function() {
    $this->actingAs($this->admin)
        ->get(Resources\AffectationResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des competence-savoirfaire', function() {
    $this->actingAs($this->admin)
        ->get(Resources\CompetenceSavoirfaireResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de competence-savoirfaire', function() {
    $this->actingAs($this->admin)
        ->get(Resources\CompetenceSavoirfaireResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des objectifs', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ObjectifResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de objectif', function() {
    $this->actingAs($this->admin)
        ->get(Resources\ObjectifResource::getUrl('create'))
        ->assertSuccessful();
});