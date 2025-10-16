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

    Models\Annee::factory()->create();
    $this->assertTrue(Models\Annee::all()->count() == 1);

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
    $annee = Models\Annee::factory()->create();
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

it('affiche le livret FCM ', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $parcours = Models\Parcours::factory()->create();
    
    // ✅ CORRECTION: Utiliser la nouvelle structure
    $annee = Models\Annee::factory()->create();
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

    Livewire::actingAs($this->admin)
            ->test(Resources\MarinResource\Pages\LivretDeFcm::class, ["record" => $marin->id])
            ->assertSee("Livret du marin");
});

it('met a jour le parcours en cas de validation', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

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

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs (nouvelle structure)
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',  // Objectif 1
        '97750a5e-abd4-4c16-89a6-65a2695ab692',  // Objectif 2
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',  // Objectif 3
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'   // Objectif 4
    ];

    foreach($objectifs as $objectif)
    {
        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid : $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid : $marin->uuid,
            detail: [
                "type_element" => "objectif", // ✅ CORRECTION: objectif au lieu d'activite
                "uuid_element" => $objectif,
                "date" => Carbon::now(),
                "commentaire"=> "commentaire"
            ]
            );
        event($event);
    }

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';
    $savoirfaire_trouve = false;

    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee)
    {
        if (!isset($annee["savoirfaires"])) continue;
        
        foreach($annee["savoirfaires"] as $savoirfaire)
        {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid)
            {
                $savoirfaire_trouve = true;
                
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif)
                    {
                        $this->assertTrue($objectif["etat_valid"] == true);
                    }
                }
                $this->assertTrue($savoirfaire["etat_valid"] == true);
            }
        }
    }
    
    $this->assertTrue($savoirfaire_trouve, "Le savoir-faire devrait être trouvé dans le parcours");
});

it('met a jour le parcours en cas d annulation', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

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

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];

    foreach($objectifs as $objectif)
    {
        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid : $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid : $marin->uuid,
            detail: [
                "type_element" => "objectif", // ✅ CORRECTION
                "uuid_element" => $objectif,
                "date" => Carbon::now(),
                "commentaire"=> "commentaire"
            ]
            );
        event($event);
    }

    // On annule le premier objectif
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee)
    {
        if (!isset($annee["savoirfaires"])) continue;
        
        foreach($annee["savoirfaires"] as $savoirfaire)
        {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid)
            {
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $savoirfaire['pivot']['id'] ?? null;
                        $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertFalse($objectif["etat_valid"]);
                        }
                        else{
                            $this->assertTrue($objectif["etat_valid"] == true);
                        }
                    }
                }
                $this->assertFalse($savoirfaire["etat_valid"]);
            }
        }
    }
});

it('met a jour le parcours en cas de proposition', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

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

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];

    // On propose le premier objectif
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee)
    {
        if (!isset($annee["savoirfaires"])) continue;
        
        foreach($annee["savoirfaires"] as $savoirfaire)
        {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid)
            {
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $savoirfaire['pivot']['id'] ?? null;
                        $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertTrue($objectif["date_proposition"] != null);
                        }
                        else{
                            $this->assertTrue($objectif["date_proposition"] == null);
                        }
                    }
                }
            }
        }
    }
});

it('met a jour le parcours en cas de validation d une proposition', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

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

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];

    // On propose le premier objectif
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    // On valide le premier objectif
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee)
    {
        if (!isset($annee["savoirfaires"])) continue;
        
        foreach($annee["savoirfaires"] as $savoirfaire)
        {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid)
            {
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $savoirfaire['pivot']['id'] ?? null;
                        $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertTrue($objectif["date_proposition"] == null);
                            $this->assertTrue($objectif["etat_valid"] == true);
                        }
                    }
                }
            }
        }
    }
});

it('met a jour le parcours en cas de refus d une proposition', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

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

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906'
    ];

    // On propose le premier objectif
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    // On refuse la proposition
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "commentaire"
        ]
        );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee)
    {
        if (!isset($annee["savoirfaires"])) continue;
        
        foreach($annee["savoirfaires"] as $savoirfaire)
        {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid)
            {
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $savoirfaire['pivot']['id'] ?? null;
                        $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertTrue($objectif["date_proposition"] == null);
                            $this->assertTrue($objectif["etat_valid"] == false);
                        }
                    }
                }
            }
        }
    }
});

it('valide automatiquement tous les objectifs quand on valide un savoirfaire', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Models\Parcours::count() == 1);
    $parcours = Models\Parcours::first();

    // Creation de parcours Event
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

    // Attribuer Parcours Marin
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
        );
    event($event);

    $savoirfaire_uuid = 'c64fcc6a-9e48-427f-9097-da13f2ddbf2d';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        '06a569b8-23a7-4c3f-b57a-51ca841894a7',
        '97750a5e-abd4-4c16-89a6-65a2695ab692',
        '6920df9c-4edc-4c4c-a1c9-f069ffa25976',
        '6ac6fa71-bc34-4165-bf2b-53a85ebb6906',
        'ed1fa3e7-22cf-442b-b9c0-3d72a9ec984b'   // Objectif hors catégorie
    ];

    // ✅ CORRECTION: Helper adapté pour la nouvelle structure
    $getObjectifsDuSavoirfaire = function($parcoursMarin, $savoirfaire_uuid) {
        $objectifs_du_savoirfaire = [];

        foreach($parcoursMarin["annees"] as $annee) {
            if (!isset($annee['savoirfaires'])) continue;

            foreach($annee['savoirfaires'] as $savoirfaire) {
                if ($savoirfaire['uuid'] === $savoirfaire_uuid) {
                    if (isset($savoirfaire['objectifs']) && is_array($savoirfaire['objectifs'])) {
                        foreach($savoirfaire['objectifs'] as $objectif) {
                            // Ajouter à la fois l'UUID simple et composite
                            $objectifs_du_savoirfaire[] = $objectif['uuid'] ?? '';
                            
                            $pivotId = $savoirfaire['pivot']['id'] ?? null;
                            if ($pivotId) {
                                $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                                $objectifs_du_savoirfaire[] = $objUuidComposite;
                            }
                        }
                    }
                    return $objectifs_du_savoirfaire;
                }
            }
        }

        return $objectifs_du_savoirfaire;
    };

    // ÉTAPE 1: Identifier les objectifs du savoir-faire
    $marin->refresh();
    $objectifs_du_savoirfaire = $getObjectifsDuSavoirfaire(
        $marin->complements_fcm->donnees_du_parcours->parcoursmarin,
        $savoirfaire_uuid
    );

    // ÉTAPE 2: Identifier les objectifs hors catégorie
    $objectifs_hors_categorie = array_diff($objectifs, $objectifs_du_savoirfaire);

    echo "\n🔍 Objectifs du savoir-faire: " . implode(', ', $objectifs_du_savoirfaire) . "\n";
    echo "🔍 Objectifs hors catégorie: " . implode(', ', $objectifs_hors_categorie) . "\n";

    // ÉTAPE 3: Valider seulement le premier objectif du savoir-faire
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "objectif", // ✅ CORRECTION
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire"=> "objectif valide individuellement"
        ]
        );
    event($event);

    // ÉTAPE 4: Valider les objectifs hors catégorie
    foreach($objectifs_hors_categorie as $objectif_hors_categorie) {
        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid : $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid : $marin->uuid,
            detail: [
                "type_element" => "objectif", // ✅ CORRECTION
                "uuid_element" => $objectif_hors_categorie,
                "date" => Carbon::now(),
                "commentaire"=> "objectif hors categorie valide"
            ]
            );
        event($event);
    }

    // ÉTAPE 5: Valider maintenant le savoir-faire complet
    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            "type_element" => "savoirfaire",
            "uuid_element" => $savoirfaire_uuid,
            "date" => Carbon::now(),
            "commentaire"=> "validation savoir-faire complet"
        ]
        );
    event($event);

    $marin->refresh();

    // ✅ VÉRIFICATION: État des objectifs dans le parcours (nouvelle structure)
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["annees"] as $annee) {
        if (!isset($annee['savoirfaires'])) continue;
        
        foreach($annee['savoirfaires'] as $savoirfaire) {
            if ($savoirfaire["uuid"] == $savoirfaire_uuid) {
                // Tous les objectifs du savoir-faire doivent être validés
                if (isset($savoirfaire["objectifs"]) && is_array($savoirfaire["objectifs"])) {
                    foreach($savoirfaire["objectifs"] as $objectif) {
                        $this->assertTrue($objectif["etat_valid"] == true,
                            "L'objectif {$objectif['uuid']} devrait être validé");

                        // Vérifier les commentaires selon l'origine de la validation
                        $pivotId = $savoirfaire['pivot']['id'] ?? null;
                        $objUuidComposite = "obj_{$pivotId}_{$objectif['id']}";
                        
                        if ($objectif['uuid'] === $objectifs[0] || $objUuidComposite === $objectifs[0]) {
                            // Le premier objectif était déjà validé
                            $this->assertEquals("objectif valide individuellement", $objectif['commentaire'],
                                "L'objectif déjà validé devrait garder son commentaire original");
                        } else {
                            // Les autres objectifs ont été validés par le savoir-faire
                            $this->assertEquals("validation savoir-faire complet", $objectif['commentaire'],
                                "L'objectif auto-validé devrait avoir le commentaire du savoir-faire");
                        }
                    }
                }
                
                // Le savoir-faire lui-même doit être validé
                $this->assertTrue($savoirfaire["etat_valid"] == true,
                    "Le savoir-faire devrait être validé");
            }
        }
    }
});
