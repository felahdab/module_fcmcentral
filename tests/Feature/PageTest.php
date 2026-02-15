<?php

use Tests\TestCase;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\GelResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeGelResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;
use function Pest\Livewire\livewire;
use function Pest\Laravel\{actingAs};

use Illuminate\Foundation\Testing\RefreshDatabase;

use Livewire\Livewire;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;

use App\Models\User;

use Modules\FcmCentral\Filament\Fcmcentral\Resources;

uses(RefreshDatabase::class);
uses(TestCase::class);

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

it('affiche la page liste des savoir faire', function() {
    $this->actingAs($this->admin)
        ->get(SavoirFaireResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de savoir faire', function() {
    $this->actingAs($this->admin)
        ->get(SavoirFaireResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des domaines', function() {
    $this->actingAs($this->admin)
        ->get(DomaineResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de domaines', function() {
    $this->actingAs($this->admin)
        ->get(DomaineResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours type', function() {
    $this->actingAs($this->admin)
        ->get(ParcoursTypeResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de parcours type', function() {
    $this->actingAs($this->admin)
        ->get(ParcoursTypeResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des cohortes', function() {
    $this->actingAs($this->admin)
        ->get(CohorteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de cohorte', function() {
    $this->actingAs($this->admin)
        ->get(CohorteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des notes', function() {
    $this->actingAs($this->admin)
        ->get(NoteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de note', function() {
    $this->actingAs($this->admin)
        ->get(NoteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des types de notes', function() {
    $this->actingAs($this->admin)
        ->get(TypeNoteResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de type de note', function() {
    $this->actingAs($this->admin)
        ->get(TypeNoteResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des etats', function() {
    $this->actingAs($this->admin)
        ->get(GelResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de etat', function() {
    $this->actingAs($this->admin)
        ->get(GelResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des types etats', function() {
    $this->actingAs($this->admin)
        ->get(TypeGelResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de type etat', function() {
    $this->actingAs($this->admin)
        ->get(TypeGelResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours figes', function() {
    $this->actingAs($this->admin)
        ->get(ParcoursSerialiseResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page liste des parcours', function() {
    $this->actingAs($this->admin)
        ->get(ParcoursResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de parcours', function() {
    $this->actingAs($this->admin)
        ->get(ParcoursResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des affectations', function() {
    $this->actingAs($this->admin)
        ->get(AffectationResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de affectations', function() {
    $this->actingAs($this->admin)
        ->get(AffectationResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des competence-savoirfaire', function() {
    $this->actingAs($this->admin)
        ->get(CompetenceSavoirfaireResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de competence-savoirfaire', function() {
    $this->actingAs($this->admin)
        ->get(CompetenceSavoirfaireResource::getUrl('create'))
        ->assertSuccessful();
});

it('affiche la page liste des objectifs', function() {
    $this->actingAs($this->admin)
        ->get(ObjectifResource::getUrl('index'))
        ->assertSuccessful();
});

it('affiche la page création de objectif', function() {
    $this->actingAs($this->admin)
        ->get(ObjectifResource::getUrl('create'))
        ->assertSuccessful();
});

it('ne genere pas d exception quand on demande a voir la page ListMarins en etant invite', function() {
    $this->get(MarinResource::getUrl('index'));

    $this->assertTrue(true);
});
