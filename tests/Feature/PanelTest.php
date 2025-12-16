<?php

use function Pest\Livewire\livewire;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use function Pest\Laravel\{actingAs};
use Livewire\Livewire;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use App\Models\User;
use App\Models\Setting;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Configuration;
use Modules\FcmCentral\Filament\Fcmcentral\Resources;
use Modules\FcmCentral\Models;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCentral\Database\Seeders\Parcours\DeasmMasterSeeder;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('FCM Central'),
    );

    $this->admin=User::factory()->create();
    $this->admin->admin=1;
    $this->admin->save();


    $this->createMarin = function() {
        $marin = Models\Marin::factory()->create();
        $marin->suivi_en_fcm = true;
        Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
        return $marin->fresh();
    };
    
    $this->createParcoursAvecDonnees = function() {
        $this->seed(DeasmMasterSeeder::class);
        return Models\Parcours::first();
    };
    
    $this->serializeParcours = function($parcours) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                "version" => 1,
                "date_de_debut" => Carbon::tomorrow(),
            ]
        ));
    };
    
    $this->assignerParcoursAMarin = function($marin, $parcours) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: ["parcours" => $parcours->uuid]
        ));
    };
    
    $this->findElementInParcours = function($parcoursMarin, $type, $uuid) {
        foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
            if ($type === 'affectation' && $affectation['uuid'] === $uuid) {
                return $affectation;
            }
            
            foreach ($affectation['competences'] ?? [] as $competence) {
                if ($type === 'competence' && $competence['uuid'] === $uuid) {
                    return $competence;
                }
                
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if ($type === 'competenceSavoirfaire' && $csf['uuid'] === $uuid) {
                        return $csf;
                    }
                    
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        if ($type === 'objectif' && $objectif['uuid'] === $uuid) {
                            return $objectif;
                        }
                    }
                }
            }
        }
        return null;
    };

});

it('affiche le panneau principal FCM Central', function() {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertSee('Tableau de bord');
});

it('affiche la page de configuration', function() {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->assertSee('Configuration du module FCM Central');
});

it('enregistre la configuration du module FcmCentral en base de données', function() {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->fillForm([
            "transmit_user_generated_events_to_remote_fcmcentral_instance" => true,
            "url_of_remote_fcmcentral_instance" => "url",
            "token_for_remote_fcmcentral_instance" => "token"
        ])
        ->call('save');

    $this->assertTrue(Setting::forKey('fcmcentral')->get('transmit_user_generated_events_to_remote_fcmcentral_instance') == true);
    $this->assertTrue(Setting::forKey('fcmcentral')->get('url_of_remote_fcmcentral_instance') == "url");
    $this->assertTrue(Setting::forKey('fcmcentral')->get('token_for_remote_fcmcentral_instance') == "token");
});

it('enregistre la configuration du module FcmCommun en base de données', function() {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->fillForm([
            "retreive_cohortes_from_distant_fcmcentral_module" => true,
            "cohortes_url_of_remote_fcmcentral_instance" => "url",
            "cohorte_token_for_remote_fcmcentral_instance" => "token"
        ])
        ->call('save');

    $this->assertTrue(Setting::forKey('fcmcommun')->get('retreive_cohortes_from_distant_fcmcentral_module') == true);
    $this->assertTrue(Setting::forKey('fcmcommun')->get('cohortes_url_of_remote_fcmcentral_instance') == "url");
    $this->assertTrue(Setting::forKey('fcmcommun')->get('cohorte_token_for_remote_fcmcentral_instance') == "token");
});

it('affiche la liste des cohortes', function() {
    Livewire::actingAs($this->admin)
        ->test(Resources\CohorteResource\Pages\ListCohortes::class)
        ->assertSee('Cohortes');
});

it('a des factories pour tous les modeles', function(){
    Models\Objectif::factory()->create();
    $this->assertTrue(Models\Objectif::all()->count() == 1);

    // Models\Competence::factory()->create();
    // $this->assertTrue(Models\Competence::all()->count() == 1);

    Models\Affectation::factory()->create();
    $this->assertTrue(Models\Affectation::all()->count() == 1);

    Models\Parcours::factory()->create();
    $this->assertTrue(Models\Parcours::all()->count() == 1);

    Models\Savoirfaire::factory()->create();
    $this->assertTrue(Models\Savoirfaire::all()->count() == 1);

    Models\Objectif::factory()->create();
    $this->assertTrue(Models\Objectif::all()->count() == 1);
});

it('genere un  UserGeneratedEvent quand on demande à figer un parcours depuis la page d edition d un parcours', function(){
    Event::fake();

    $this->assertTrue(Models\Parcours::all()->count() == 0);
    Models\Parcours::factory()->create();

    $parcours = Models\Parcours::first();
    $date_de_debut = Carbon::now()->tomorrow();

    Livewire::actingAs($this->admin)
        ->test(Resources\ParcoursResource\Pages\EditParcours::class, ["record" => $parcours->id])
        ->mountAction('figer')
        ->callAction('figer', data: [
            'date de debut' => $date_de_debut,
            'version' => 1,
        ])
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($parcours, $date_de_debut){
        return $event->object_class == 'Parcours' && $event->object_uuid == $parcours->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on demande à figer un parcours depuis la liste des parcours', function(){
    Event::fake();

    $this->assertTrue(Models\Parcours::all()->count() == 0);
    Models\Parcours::factory()->create();

    $parcours = Models\Parcours::first();
    $date_de_debut = Carbon::now()->tomorrow();

    Livewire::actingAs($this->admin)
        ->test(Resources\ParcoursResource\Pages\ListParcours::class)
        ->callTableAction('serializeParcours', $parcours, data: [
            'date de debut' => $date_de_debut,
            'version' => 1,
        ])
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($parcours, $date_de_debut){
        return $event->object_class == 'Parcours' && $event->object_uuid == $parcours->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on demande à suivre un marin en FCM', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $this->assertTrue(Models\Marin::all()->count() == 1);

    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        ->assertCanNotSeeTableRecords([$marin])
        ->removeTableFilter('en_fcm')
        ->assertCanSeeTableRecords([$marin])
        ->callTableAction('suivre_en_fcm', $marin)
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::MARIN_EN_FCM->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on demande a ne plus suivre un marin en FCM', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $marin->suivi_en_fcm = true;
    $marin = Models\Marin::find($marin->id);

    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        ->assertCountTableRecords(1)
        ->assertCanSeeTableRecords([$marin])
        ->callTableAction('ne_plus_suivre_en_fcm', $marin)
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::MARIN_PAS_FCM->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on assigne une cohorte a un marin suivi en fcm', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $marin->suivi_en_fcm = true;

    $cohorte = Models\Cohorte::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        ->callTableAction('attributs-fcm', $marin, data: [
            "cohorte_id" => $cohorte->uuid,
            "mentor_id" => null,
            "parcours_id" => null
        ])
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on assigne un mentor a un marin suivi en fcm', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    $this->assertTrue(Models\Marin::all()->count() == 1);
    $mentor = Models\Marin::factory()->create();
    $this->assertTrue(Models\Marin::all()->count() == 2);

    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        ->callTableAction('attributs-fcm', $marin, data: [
            "cohorte_id" => null,
            "mentor_id" => $mentor->uuid,
            "parcours_id" => null
        ])
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on assigne un parcours a un marin suivi en fcm', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $parcours = Models\Parcours::factory()->create();
    
    // ✅ CORRECTION: Utiliser la nouvelle structure
    $annee = Models\Affectation::factory()->create();
    $parcours->annees()->attach($annee);

    $savoirfaire = Models\Savoirfaire::factory()->create();
    $annee->savoirfaires()->attach($savoirfaire);

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid : $parcours->uuid,
        detail: [
            "version" => 1,
            "date_de_debut"=> Carbon::tomorrow(),
        ]
        );
    event($event);

    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        ->callTableAction('attributs-fcm', $marin, data: [
            "cohorte_id" => null,
            "mentor_id" => null,
            "parcours_id" => $parcours->uuid
        ])
        ->assertHasNoActionErrors();

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});




// ========================================
// TESTS VALIDATION PARCOURS (NOUVELLE STRUCTURE)
// ========================================

// Dataset avec les UUIDs de tes seeders
dataset('objectifs_deasm', function() {
    return [
        ['uuid' => '06a569b8-23a7-4c3f-b57a-51ca841894a7'],
        ['uuid' => '97750a5e-abd4-4c16-89a6-65a2695ab692'],
        ['uuid' => '6920df9c-4edc-4c4c-a1c9-f069ffa25976'],
        ['uuid' => '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'],
    ];
});

dataset('competenceSavoirfaire_uuid', fn() => ['c64fcc6a-9e48-427f-9097-da13f2ddbf2d']);

it('met à jour le parcours en cas de validation d\'objectifs', function(){
    $marin = ($this->createMarin)();
    $parcours = ($this->createParcoursAvecDonnees)();
    ($this->serializeParcours)($parcours);
    ($this->assignerParcoursAMarin)($marin, $parcours);
    
    // Valider tous les objectifs du dataset
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];
    
    foreach($objectifs as $objectif_uuid) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                "type_element" => "objectif",
                "uuid_element" => $objectif_uuid,
                "date" => Carbon::now(),
                "commentaire" => "validation objectif"
            ]
        ));
    }
    
    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
    // Vérifier que tous les objectifs sont validés
    foreach($objectifs as $objectif_uuid) {
        $objectif = ($this->findElementInParcours)($parcoursMarin, 'objectif', $objectif_uuid);
        expect($objectif)->not->toBeNull();
        expect($objectif['etat_valid'])->toBeTrue();
    }
    
    // Vérifier que le competenceSavoirfaire parent est validé si tous ses objectifs le sont
    $csf_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';
    $csf = ($this->findElementInParcours)($parcoursMarin, 'competenceSavoirfaire', $csf_uuid);
    
    if ($csf) {
        $allObjectifsValid = collect($csf['objectifs'] ?? [])
            ->every(fn($obj) => $obj['etat_valid'] ?? false);
        
        expect($csf['etat_valid'])->toBe($allObjectifsValid);
    }
});

it('met à jour le parcours en cas d\'annulation d\'objectif', function(){
    $marin = ($this->createMarin)();
    $parcours = ($this->createParcoursAvecDonnees)();
    ($this->serializeParcours)($parcours);
    ($this->assignerParcoursAMarin)($marin, $parcours);
    
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];
    
    // Valider tous les objectifs
    foreach($objectifs as $objectif_uuid) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                "type_element" => "objectif",
                "uuid_element" => $objectif_uuid,
                "date" => Carbon::now(),
                "commentaire" => "validation"
            ]
        ));
    }
    
    // Annuler le premier objectif
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "objectif",
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire" => "annulation"
        ]
    ));
    
    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
    // Vérifier que l'objectif annulé n'est plus validé
    $objectif = ($this->findElementInParcours)($parcoursMarin, 'objectif', $objectifs[0]);
    expect($objectif['etat_valid'])->toBeFalse();
    
    // Vérifier que le CSF parent n'est plus validé
    $csf_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';
    $csf = ($this->findElementInParcours)($parcoursMarin, 'competenceSavoirfaire', $csf_uuid);
    
    if ($csf) {
        expect($csf['etat_valid'])->toBeFalse();
    }
});

it('met à jour le parcours en cas de proposition d\'objectif', function(){
    $marin = ($this->createMarin)();
    $parcours = ($this->createParcoursAvecDonnees)();
    ($this->serializeParcours)($parcours);
    ($this->assignerParcoursAMarin)($marin, $parcours);
    
    $objectif_uuid = '06a569b8-23a7-4c3f-b57a-51ca841894a7';
    
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "objectif",
            "uuid_element" => $objectif_uuid,
            "date" => Carbon::now(),
            "commentaire" => "proposition"
        ]
    ));
    
    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
    $objectif = ($this->findElementInParcours)($parcoursMarin, 'objectif', $objectif_uuid);
    
    expect($objectif)->not->toBeNull();
    expect($objectif['date_proposition'])->not->toBeNull();
    expect($objectif['etat_valid'])->toBeFalse();
});

it('valide automatiquement tous les objectifs quand on valide un competenceSavoirfaire', function(){
    $marin = ($this->createMarin)();
    $parcours = ($this->createParcoursAvecDonnees)();
    ($this->serializeParcours)($parcours);
    ($this->assignerParcoursAMarin)($marin, $parcours);
    
    $csf_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';
    
    // Valider le competenceSavoirfaire complet
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "competenceSavoirfaire",
            "uuid_element" => $csf_uuid,
            "date" => Carbon::now(),
            "commentaire" => "validation CSF complet"
        ]
    ));
    
    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
    $csf = ($this->findElementInParcours)($parcoursMarin, 'competenceSavoirfaire', $csf_uuid);
    
    expect($csf)->not->toBeNull();
    expect($csf['etat_valid'])->toBeTrue();
    
    // Vérifier que tous les objectifs sont validés
    foreach($csf['objectifs'] ?? [] as $objectif) {
        expect($objectif['etat_valid'])->toBeTrue();
        expect($objectif['commentaire'])->toBe("validation CSF complet");
    }
});

it('calcule correctement le pourcentage de validation du parcours', function(){
    $marin = ($this->createMarin)();
    $parcours = ($this->createParcoursAvecDonnees)();
    ($this->serializeParcours)($parcours);
    ($this->assignerParcoursAMarin)($marin, $parcours);
    
    $csf_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';
    
    // Valider un CSF
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "competenceSavoirfaire",
            "uuid_element" => $csf_uuid,
            "date" => Carbon::now(),
            "commentaire" => "validation"
        ]
    ));
    
    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
    // Calculer le pourcentage selon la nouvelle logique
    $total = 0;
    $valid = 0;
    
    foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                $total++;
                if ($csf['etat_valid'] ?? false) $valid++;
                
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    $total++;
                    if ($objectif['etat_valid'] ?? false) $valid++;
                }
            }
        }
    }
    
    $pourcentage = $total > 0 ? round(($valid / $total) * 100, 0) : 0;
    
    expect($pourcentage)->toBeGreaterThan(0);
    expect($total)->toBeGreaterThan(0);
});
