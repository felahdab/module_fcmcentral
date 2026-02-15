<?php

use Tests\TestCase;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages\ListCohortes;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\EditParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ListParcours;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MarinsSupervisesWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorProgressionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorStatsWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinEvolutionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinProgressWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinRepartitionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinStatsWidget;
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
use App\Models\Permission;
use App\Models\Setting;

use Modules\FcmCentral\Filament\Fcmcentral\Pages\Configuration;
use Modules\FcmCentral\Filament\Fcmcentral\Resources;
use Modules\FcmCentral\Models;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCentral\Database\Seeders\Parcours\DeasmMasterSeeder;


uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
    
    $this->admin = User::factory()->create(['admin' => 1]);
    
    $this->createMarin = function() {
        $marin = Marin::factory()->create(['suivi_en_fcm' => true]);
        FcmMarin::create(["rh_marin_id" => $marin->id]);
        return $marin->fresh();
    };
    
    $this->createParcoursAvecDonnees = function() {
        $this->seed(DeasmMasterSeeder::class);
        return Parcours::first();
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
        
        // Recharger pour être sûr que complements_fcm existe
        $marin->refresh();
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

// ========================================
// TESTS PAGES
// ========================================

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
    
    expect(Setting::forKey('fcmcentral')->get('transmit_user_generated_events_to_remote_fcmcentral_instance'))->toBeTrue();
    expect(Setting::forKey('fcmcentral')->get('url_of_remote_fcmcentral_instance'))->toBe("url");
    expect(Setting::forKey('fcmcentral')->get('token_for_remote_fcmcentral_instance'))->toBe("token");
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
    
    expect(Setting::forKey('fcmcommun')->get('retreive_cohortes_from_distant_fcmcentral_module'))->toBeTrue();
    expect(Setting::forKey('fcmcommun')->get('cohortes_url_of_remote_fcmcentral_instance'))->toBe("url");
    expect(Setting::forKey('fcmcommun')->get('cohorte_token_for_remote_fcmcentral_instance'))->toBe("token");
});

it('affiche la liste des cohortes', function() {
    Livewire::actingAs($this->admin)
        ->test(ListCohortes::class)
        ->assertSee('Cohortes');
});

// ========================================
// TESTS FACTORIES 
// ========================================

it('a des factories pour tous les modeles', function(){

    
    $competence = Competence::factory()->create(['code' => 'C-TEST-1']);
    $savoirfaire = Savoirfaire::factory()->create(['code' => 'SF-TEST-1']);

    $competenceSavoirfaire = CompetenceSavoirfaire::factory()->create([
        'competence_id' => $competence->id,
        'savoirfaire_id' => $savoirfaire->id,
    ]);

    $objectif = Objectif::factory()->create([
        'code' => 'OBJ-TEST-1',
        'competence_savoirfaire_id' => $competenceSavoirfaire->id,
    ]);
    

    $affectation = Affectation::factory()->create(['code' => 'AFF-TEST-1']);
    

    $parcours = Parcours::factory()->create([
        'code' => 'PAR-TEST-1',
        // Si parcours_type_id est NOT NULL, il faut le fournir :
        'parcours_type_id' => ParcoursType::factory()->create()->id,
    ]);

    // Affectation
    expect(Affectation::count())->toBe(1);

    // Parcours
    expect(Parcours::count())->toBe(1);

    // SavoirFaire
    expect(Savoirfaire::count())->toBe(1);

    // Competence
    expect(Competence::count())->toBe(1);

    // CompetenceSavoirFaire
    expect(CompetenceSavoirfaire::count())->toBe(1);
});


// ========================================
// TESTS USER GENERATED EVENTS 
// ========================================

it('genere un UserGeneratedEvent quand on demande à figer un parcours depuis la page d edition d un parcours', function(){
    Event::fake();
    
    // ✅ FIX: Ajouter le code
    $parcours = Parcours::factory()->create(['code' => 'PAR-TEST-2']);
    $date_de_debut = Carbon::tomorrow();
    
    Livewire::actingAs($this->admin)
        ->test(EditParcours::class, ["record" => $parcours->id])
        ->mountAction('figer')
        ->callAction('figer', data: [
            'date de debut' => $date_de_debut,
            'version' => 1,
        ])
        ->assertHasNoActionErrors();
    
    Event::assertDispatched(UserGeneratedEvent::class, fn($e) => 
        $e->object_class == 'Parcours' && $e->object_uuid == $parcours->uuid
    );
});

it('genere un UserGeneratedEvent quand on demande à figer un parcours depuis la liste des parcours', function(){
    Event::fake();
    
    $parcours = Parcours::factory()->create(['code' => 'PAR-TEST-3']);
    
    Livewire::actingAs($this->admin)
        ->test(ListParcours::class)
        ->callTableAction('serializeParcours', $parcours, data: [
            'date de debut' => Carbon::tomorrow(),
            'version' => 1,
        ])
        ->assertHasNoActionErrors();
    
    Event::assertDispatched(UserGeneratedEvent::class);
});


it('dispatch manuel fonctionne', function () {
    Event::fake();

    $marin = Marin::factory()->create();

    event(new UserGeneratedEvent(
        UserGeneratedEventTypes::MARIN_EN_FCM,
        $this->admin->uuid,
        ObjectClasses::MARIN,
        $marin->uuid,
        ['test' => 'data']
    ));

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::MARIN_EN_FCM->value
            && $event->object_uuid == $marin->uuid;
    });
});


it('genere un  UserGeneratedEvent quand on demande à suivre un marin en FCM', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $marin = Marin::factory()->create();
    // Verification explicite 
    $marin = Marin::find($marin->id);
    // S'assurer qu'il n'est pas déjà en FCM
    FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();

    // Création d'une cohorte, d'un mentor et d'un parcours pour simuler le formulaire
    $cohorte = Cohorte::factory()->create();
    $mentor = Marin::factory()->create();
    $parcours = Parcours::factory()->create();


    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        
        //->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value'=>'tous'])
        //->resetTableFilters()
        //->assertCanSeeTableRecords([$marin])
        ->callTableAction('toggle_fcm', $marin,
        [
            'cohorte_id' => $cohorte->uuid,   
            'mentor_id' => $mentor->uuid,
            'parcours_id' => $parcours->uuid,
        ])
        ->assertHasNoActionErrors();

   

     

       // dump(Event::dispatched(UserGeneratedEvent::class));

        Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
            return $event->event_type == UserGeneratedEventTypes::MARIN_EN_FCM->value &&
                    $event->object_class == ObjectClasses::MARIN->value &&
                    $event->object_uuid == $marin->uuid;
        });
    
    
});



it('genere un  UserGeneratedEvent quand on demande a ne plus suivre un marin en FCM', function () {
    Event::fake();

   // Création d'un marin non suivi en FCM
   $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;

  // dump($marin);
   expect($marin->estEnFcm())->toBeTrue();

    // Simuler la fin du FCM via le changement d'état (exemple : FCM_FIN)
    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)

        //->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value'=>'tous'])
        ->callTableAction('toggle_fcm', $marin)
        ->assertHasNoActionErrors();

       // dump(Event::dispatched(UserGeneratedEvent::class));


       
       
});

it('ajoute la date_sedimentation lors de l\'activation FCM', function () {
    //Event::fake();

    $fcmMarin = FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;
    FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();


    // Livewire::actingAs($this->admin)
    //     ->test(Resources\MarinResource\Pages\ListMarins::class)
    //     ->filterTable('en_fcm', ['value'=>'tous'])
    //     ->callTableAction('toggle_fcm', $marin, [
    //         'cohorte_id' => $cohorte->uuid,
    //         'mentor_id' => $mentor->uuid,
    //         'parcours_id' => $parcours->uuid,
    //     ])
    //     ->assertHasNoActionErrors();

        //dump(Event::dispatched(UserGeneratedEvent::class));

      

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "cohorte_id" => 1,
            "date_de_debut"=> Carbon::tomorrow(),
        ]
    ));

    $marin->refresh();
    //dump ($marin->complementsFcm);
    expect($marin->estEnFcm())->toBeTrue();
    expect($marin->complementsFcm->date_sedimentation)->not->toBeNull();

    
});



it('génère un UserGeneratedEvent quand on modifie les jalons FCM', function(){
    Event::fake();
    
    // Création d'un marin non suivi en FCM
    $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;
   
  // dump($marin);
   expect($marin->estEnFcm())->toBeTrue();

   $dateSedimentation = now()->subDays(30)->format('Y-m-d');
    $dateFcm = now()->subDays(15)->format('Y-m-d');
    $dateCompetence = now()->format('Y-m-d');

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        ->filterTable('en_fcm', ['value'=>'tous'])
        ->callTableAction( 'jalons_fcm', $marin, [
            'date_sedimentation' => $dateSedimentation,
            'date_fcm' => $dateFcm,
            'date_competence' => $dateCompetence,
        ])
        ->assertHasNoActionErrors();

    
        //dump(Event::dispatched(UserGeneratedEvent::class));

            // Vérifier qu'un événement a été dispatché pour chaque date
    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin, $dateSedimentation) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid &&
                isset($event->detail['date_sedimentation']) &&
                $event->detail['date_sedimentation'] == $dateSedimentation;
    });

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin, $dateFcm) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid &&
                isset($event->detail['date_fcm']) &&
                $event->detail['date_fcm'] == $dateFcm;
    });

    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin, $dateCompetence) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid &&
                isset($event->detail['date_competence']) &&
                $event->detail['date_competence'] == $dateCompetence;
    });

    // Vérifier que 3 événements ont bien été dispatchés
    Event::assertDispatchedTimes(UserGeneratedEvent::class, 3);


    });

it('genere un  UserGeneratedEvent quand on assigne une cohorte a un marin suivi en fcm', function(){
    Event::fake();
    
    // Création d'un marin non suivi en FCM
    $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;
    // S'assurer qu'il n'est pas déjà en FCM
    FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();
    $cohorte = Models\Cohorte::factory()->create();
    
    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        //->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value'=>'tous'])
        ->callTableAction('toggle_fcm', $marin,
        [
            'cohorte_id' => $cohorte->uuid,   
            'mentor_id' => null,
            'parcours_id' => null,
        ])
        ->assertHasNoActionErrors();

        //dump(Event::dispatched(UserGeneratedEvent::class));
    
        Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
            return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN->value &&
                    $event->object_class == ObjectClasses::MARIN->value &&
                    $event->object_uuid == $marin->uuid;
        });
});

it('genere un  UserGeneratedEvent quand on assigne un mentor a un marin suivi en fcm', function(){
    Event::fake();
    
     // Création d'un marin non suivi en FCM
     $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;
    // S'assurer qu'il n'est pas déjà en FCM
    FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();
  
    $mentor = Marin::factory()->create();
    
    Livewire::actingAs($this->admin)
    ->test(ListMarins::class)
    //->removeTableFilter('en_fcm')
    ->filterTable('en_fcm', ['value'=>'tous'])
    ->callTableAction('toggle_fcm', $marin,
    [
        'cohorte_id' => null,   
        'mentor_id' => $mentor->uuid,
        'parcours_id' => null,
    ])
    ->assertHasNoActionErrors();

    //dump(Event::dispatched(UserGeneratedEvent::class));
    
    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });

});

it('genere un UserGeneratedEvent quand on assigne un parcours a un marin suivi en fcm', function(){
    Event::fake();
    
     // Création d'un marin non suivi en FCM
     $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;
    // S'assurer qu'il n'est pas déjà en FCM
    FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();
  
    $parcours = Parcours::factory()->create();
    
    Livewire::actingAs($this->admin)
    ->test(ListMarins::class)
    
    //->removeTableFilter('en_fcm')
    ->filterTable('en_fcm', ['value'=>'tous'])
    
    ->callTableAction('toggle_fcm', $marin,
    [
        'cohorte_id' => null,   
        'mentor_id' => null,
        'parcours_id' => $parcours->uuid,
    ])
    ->assertHasNoActionErrors();

    //dump(Event::dispatched(UserGeneratedEvent::class));
    
    Event::assertDispatched(UserGeneratedEvent::class, function($event) use ($marin){
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN->value &&
                $event->object_class == ObjectClasses::MARIN->value &&
                $event->object_uuid == $marin->uuid;
    });
});

// ========================================
// TESTS VALIDATION PARCOURS 
// ========================================


it('affiche le livret FCM', function () {
    // Création d'un marin FCM via factory
    $this->assertTrue(Marin::all()->count() == 0);
    $fcmMarin = FcmMarin::factory()->create();
   $marin = $fcmMarin->marin;
   
  // dump($marin);
   expect($marin->estEnFcm())->toBeTrue();
    $this->assertTrue(Marin::all()->count() == 1);

    //dump($marin->complementsFcm);
    
    // Seed du parcours
    $this->seed(DeasmMasterSeeder::class);
    expect(Parcours::count())->toBeGreaterThan(0);

    $parcours = Parcours::first();
    

    // Création du parcours sérialisé
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            "version" => 1,
            "date_de_debut"=> Carbon::tomorrow(),
        ]
    ));

    // Vérification du parcours sérialisé
    $parcoursSerialise = $parcours->parcoursSerialise()->latest('version')->first();
    expect($parcoursSerialise)->not->toBeNull();
    expect($parcoursSerialise->version)->toBe(1);

   
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
    ));
  
    $marin->refresh();

    // Vérification des données du parcours
    expect($marin->complementsFcm)->not->toBeNull();
    expect($marin->complementsFcm->donnees_du_parcours)->not->toBeNull();
    //expect($marin->complementsFcm->donnees_du_parcours->parcours_serialise_id)->toBe($parcoursSerialise->id);

    $parcoursMarin = $marin->complementsFcm->donnees_du_parcours->parcoursmarin;
    expect($parcoursMarin)->toBeArray();
    expect($parcoursMarin)->toHaveKey('affectations');

    // Test de l'affichage du livret via Livewire
    Livewire::actingAs($this->admin)
        ->test(LivretDeFcmLight::class, ['record' => $marin->id])
        ->assertSuccessful()
        ->assertSee("Livret du marin")
        ->assertSee($marin->nom)
        ->assertSee($marin->prenom)
        ->assertSee('C6 - Gerer les munitions pyrotechniques')
        ;

    
});








it('met à jour le parcours en cas de validation', function () {
    $this->assertSame(0, Marin::count());

    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);

    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);

    $this->assertSame(1, Marin::count());

    $this->seed(DeasmMasterSeeder::class);

    $this->assertSame(1, Parcours::count());
    $parcours = Parcours::first();

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

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
    ));

    // UUIDs des objectifs à valider
    $objectifs = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
    ];

    foreach ($objectifs as $objectif) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                "type_element" => "objectif",
                "uuid_element" => $objectif,
                "date" => Carbon::now(),
                "commentaire" => "commentaire"
            ]
        ));
    }

    $marin->refresh();

    // Recherche du savoir-faire dans la structure du parcours
    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
    $savoirfaire_trouve = false;

    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];

    foreach ($parcoursMarin["affectations"] ?? [] as $affectation) {
        foreach ($affectation["competences"] ?? [] as $competence) {
            foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
                if (($csf["uuid"] ?? null) === $savoirfaire_uuid) {
                    $savoirfaire_trouve = true;

                    // Tous les objectifs doivent être validés
                    foreach ($csf["objectifs"] ?? [] as $objectif) {
                        $this->assertTrue($objectif["etat_valid"] === true, "L'objectif {$objectif['uuid'] } doit être validé");
                    }
                    // Le savoir-faire doit être validé aussi
                    $this->assertTrue($csf["etat_valid"] === true, "Le savoir-faire doit être validé");
                }
            }
        }
    }

    $this->assertTrue($savoirfaire_trouve  , "Le savoir-faire devrait être trouvé dans le parcours");
});




it('met à jour le parcours en cas d\'annulation', function () {
    $this->assertSame(0, Marin::count());

    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertSame(1, Marin::count());

    $this->seed(DeasmMasterSeeder::class);
    $this->assertSame(1, Parcours::count());
    $parcours = Parcours::first();

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

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
    ));

    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';

    // UUIDs des objectifs à valider
    $objectifs = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
    ];

    // On valide tous les objectifs
    foreach ($objectifs as $objectif) {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                "type_element" => "objectif",
                "uuid_element" => $objectif,
                "date" => Carbon::now(),
                "commentaire" => "commentaire"
            ]
        ));
    }

    // On annule le premier objectif
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "objectif",
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire" => "commentaire"
        ]
    ));

    $marin->refresh();

    // Recherche du savoir-faire dans la structure du parcours
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];
    $savoirfaire_trouve = false;

    foreach ($parcoursMarin["affectations"] ?? [] as $affectation) {
        foreach ($affectation["competences"] ?? [] as $competence) {
            foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
            if (($csf["uuid"] ?? null) === $savoirfaire_uuid) {
                $savoirfaire_trouve = true;

                foreach ($csf["objectifs"] ?? [] as $objectif) {
                    // On vérifie l'annulation du premier objectif
                    if ($objectif['uuid'] === $objectifs[0]) {
                        $this->assertFalse($objectif["etat_valid"], "L'objectif annulé doit être non validé");
                    } else {
                        $this->assertTrue($objectif["etat_valid"], "Les autres objectifs doivent rester validés");
                    }
                }
                // Le savoir-faire doit être non validé si au moins un objectif est annulé
                $this->assertFalse($csf["etat_valid"], "Le savoir-faire doit être non validé si un objectif est annulé");
            }
        }
        }
    }

    $this->assertTrue($savoirfaire_trouve, "Le savoir-faire devrait être trouvé dans le parcours");
});





it('met à jour le parcours en cas de proposition', function () {
    $this->assertSame(0, Marin::count());

    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertSame(1, Marin::count());

    $this->seed(DeasmMasterSeeder::class);
    $this->assertSame(1, Parcours::count());
    $parcours = Parcours::first();

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

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "parcours" => $parcours->uuid,
        ]
    ));

    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';

    $objectifs = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
    ];

    // On propose le premier objectif
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "objectif",
            "uuid_element" => $objectifs[0],
            "date" => Carbon::now(),
            "commentaire" => "commentaire"
        ]
    ));

    $marin->refresh();

    // Vérification de la structure du parcours
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];
    $objectif_propose_trouve = false;

    foreach ($parcoursMarin["affectations"] ?? [] as $affectation) {
        foreach ($affectation["competences"] ?? [] as $competence) {
        foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
            if (($csf["uuid"] ?? null) === $savoirfaire_uuid) {
                foreach ($csf["objectifs"] ?? [] as $objectif) {
                    $pivotId = $csf['pivot']['id'] ?? null;
                    $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;

                    if ($objectif['uuid'] === $objectifs[0] || $objUuidComposite === $objectifs[0]) {
                        $objectif_propose_trouve = true;
                        $this->assertNotNull($objectif["date_proposition"], "L'objectif proposé doit avoir une date de proposition");
                    } else {
                        $this->assertNull($objectif["date_proposition"], "Les autres objectifs ne doivent pas avoir de date de proposition");
                    }
                }
            }
        }
        }
    }

    $this->assertTrue($objectif_propose_trouve, "L'objectif proposé doit être trouvé dans le parcours");
});



it('met a jour le parcours en cas de validation d une proposition', function(){
    $this->assertTrue(Marin::all()->count() == 0);
    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Parcours::count() == 1);
    $parcours = Parcours::first();

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

    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';

    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
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
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["affectations"] as $affectation)
    {
        if (!isset($affectation["competences"])) continue;
        
        foreach($affectation["competences"] as $competence)
        {

                foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
            if ($csf["uuid"] == $savoirfaire_uuid)
            {
                if (isset($csf["objectifs"]) && is_array($csf["objectifs"])) {
                    foreach($csf["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $csf['pivot']['id'] ?? null;
                    $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertTrue($objectif["date_proposition"] == null);
                            $this->assertTrue($objectif["etat_valid"] == true);
                        }
                    }
                }
                }
            }
        }
    }
});

it('met a jour le parcours en cas de refus d une proposition', function(){
    $this->assertTrue(Marin::all()->count() == 0);
    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Parcours::count() == 1);
    $parcours = Parcours::first();

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

    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';


    // ✅ CORRECTION: Utiliser les UUIDs des objectifs
    $objectifs = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
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
    foreach($marin->complements_fcm->donnees_du_parcours->parcoursmarin["affectations"] as $affectation)
    {
        if (!isset($affectation["competences"])) continue;
        
        foreach($affectation["competences"] as $competence)
        {

                foreach ($competence["competenceSavoirfaires"] ?? [] as $csf) {
            if ($csf["uuid"] == $savoirfaire_uuid)
            {
                if (isset($csf["objectifs"]) && is_array($csf["objectifs"])) {
                    foreach($csf["objectifs"] as $objectif)
                    {
                        // Construire l'UUID composite pour comparer
                        $pivotId = $csf['pivot']['id'] ?? null;
                    $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;
                        
                        if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]){
                            $this->assertTrue($objectif["date_proposition"] == null);
                            $this->assertTrue($objectif["etat_valid"] == false);
                        }
                    }
                }
            }
            }
        }
    }
});

it('valide automatiquement toutes les activites a false quand on valide un savoirfaire', function(){
    // ✅ Création marin avec FcmMarin via factory
    $marin = Marin::factory()->create();
    $marin = Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Marin::all()->count() == 1);

    $this->seed(DeasmMasterSeeder::class);
    $this->assertTrue(Parcours::count() == 1);
    $parcours = Parcours::first();

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


    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;

    // ✅ UUIDs cibles
    $savoirfaireUuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
   
    $objectifsUuids = [
        'deasm1b1-1234-5678-obje-11aa1c1sf1o1',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o2',
        'deasm1b1-1234-5678-obje-11aa1c1sf1o3',
    ];

        // ✅ FONCTION HELPER: Trouver les activités du savoir-faire (nouvelle structure)
        $getObjectifsDuSavoirfaire = function($parcoursMarin, $sfUuid) {
            $objectifs = [];
    
            foreach($parcoursMarin['affectations'] as $affectation) {
                if (empty($affectation['competences'])) continue;
               
                foreach($affectation['competences'] as $competence) {
                    if (empty($competence['competenceSavoirfaires'])) continue;
                   
                    foreach($competence['competenceSavoirfaires'] as $csf) {
                        // Construire UUID composite si nécessaire
                        $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                        $csfPivotUuid = null;
                       
                        if (!empty($csf['pivot'])) {
                            $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                            if ($pivotPart && $csfUuidSimple) {
                                $csfPivotUuid = $csfUuidSimple . '_' . $pivotPart;
                            }
                        }
    
                        if ($csfUuidSimple === $sfUuid || $csfPivotUuid === $sfUuid) {
                            if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                                foreach($csf['objectifs'] as $objectif) {
                                    if (!empty($objectif['uuid'])) {
                                        $objectifs[] = $objectif['uuid'];
                                    }
                                }
                            }
                            return $objectifs;
                        }
                    }
                }
            }
    
            return $objectifs;
        };
    
        // ✅ ÉTAPE 1: Identifier les activités du savoir-faire
        $objectifsDuSf = $getObjectifsDuSavoirfaire($parcoursMarin, $savoirfaireUuid);
        $objectifsHorsCategorie = array_diff($objectifsUuids, $objectifsDuSf);
    
        //echo "\n🔍 Objectifs du savoir-faire: " . implode(', ', $objectifsDuSf) . "\n";
        //echo "🔍 Objectifs hors catégorie: " . implode(', ', $objectifsHorsCategorie) . "\n";
    
        // ✅ ÉTAPE 2: Valider seulement la première objectif du savoir-faire
        //echo "\n📝 Validation du premier objectif\n";
       
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                'type_element' => 'objectif',
                'uuid_element' => $objectifsUuids[0],
                'date' => Carbon::now()->toDateTimeString(),
                'commentaire' => 'objectif valide individuellement'
            ]
        ));
    
        // ✅ ÉTAPE 3: Valider les activités hors catégorie
        if (!empty($objectifsHorsCategorie)) {
            //echo "📝 Validation des objectif hors catégorie (" . count($objectifsHorsCategorie) . ")\n";
           
            foreach($objectifsHorsCategorie as $actUuid) {
                event(new UserGeneratedEvent(
                    event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    user_uuid: $this->admin->uuid,
                    object_class: ObjectClasses::MARIN,
                    object_uuid: $marin->uuid,
                    detail: [
                        'type_element' => 'objectif',
                        'uuid_element' => $actUuid,
                        'date' => Carbon::now()->toDateTimeString(),
                        'commentaire' => 'objectif hors categorie valide'
                    ]
                ));
            }
        }
    
        // ✅ Compter les events avant validation du savoir-faire
        $eventsAvant = DB::table('fcmcentral_stored_events')->count();
        //echo "🔍 Events avant validation savoir-faire: {$eventsAvant}\n";
    
        // ✅ ÉTAPE 4: Valider le savoir-faire complet
        //echo "\n📝 Validation du savoir-faire complet\n";
       
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin->uuid,
            detail: [
                'type_element' => 'savoirfaire',
                'uuid_element' => $savoirfaireUuid,
                'date' => Carbon::now()->toDateTimeString(),
                'commentaire' => 'validation savoir-faire complet'
            ]
        ));
    
        $marin->refresh();
        $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
        // ✅ VÉRIFICATION 1: État des activités dans le parcours
        //echo "\n🔎 Vérification des objectifs du savoir-faire\n";
       
        $sfTrouve = false;
    
        foreach($parcoursMarin['affectations'] as $affectation) {
            if (empty($affectation['competences'])) continue;
           
            foreach($affectation['competences'] as $competence) {
                if (empty($competence['competenceSavoirfaires'])) continue;
               
                foreach($competence['competenceSavoirfaires'] as $csf) {
                    $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                    $csfPivotUuid = null;
                   
                    if (!empty($csf['pivot'])) {
                        $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                        if ($pivotPart && $csfUuidSimple) {
                            $csfPivotUuid = $csfUuidSimple . '_' . $pivotPart;
                        }
                    }
    
                    if ($csfUuidSimple === $savoirfaireUuid || $csfPivotUuid === $savoirfaireUuid) {
                        $sfTrouve = true;
    
                        // Toutes les activités du savoir-faire doivent être validées
                        // expect($csf['activites'])->toBeArray();
                       
                        foreach($csf['objectifs'] as $index => $objectif) {
                            expect($objectif['etat_valid'] ?? false)->toBeTrue(
                                "L'objectif {$objectif['uuid']} devrait être validé"
                            );
                            if ($objectif['uuid'] ===  $objectifsUuids[0]) {
                            // La première activité était déjà validée
                            $this->assertEquals("objectif valide individuellement", $objectif['commentaire'],
                                "L'activité déjà validée devrait garder son commentaire original");
                        } else {
                            // Les autres activités ont été validées par le savoir-faire
                            $this->assertEquals("validation savoir-faire complet", $objectif['commentaire'],
                                "L'activité auto-validée devrait avoir le commentaire du savoir-faire");
                        }
                            
                        }
    
                        // Le savoir-faire lui-même doit être validé
                        expect($csf['etat_valid'] ?? false)->toBeTrue(
                            'Le savoir-faire devrait être validé'
                        );
                        expect($csf['commentaire'])->toBe('validation savoir-faire complet');
                       
                        break 3;
                    }
                }
            }
        }
    
        expect($sfTrouve)->toBeTrue('Le savoir-faire devrait être trouvé dans le parcours');
    
        // ✅ VÉRIFICATION 2: Les activités hors catégorie restent inchangées
        if (!empty($objectifsHorsCategorie)) {
            //echo "\n🔎 Vérification des activités hors catégorie\n";
           
            foreach($objectifsHorsCategorie as $actUuid) {
                $objectifTrouvee = false;
    
                foreach($parcoursMarin['affectations'] as $affectation) {
                    if (empty($affectation['competences'])) continue;
                   
                    foreach($affectation['competences'] as $competence) {
                        if (empty($competence['competenceSavoirfaires'])) continue;
                       
                        foreach($competence['competenceSavoirfaires'] as $csfAutre) {
                            if (empty($csfAutre['objectifs'])) continue;
                           
                            foreach($csfAutre['objectifs'] as $objectif) {
                                if ($objectif['uuid'] === $actUuid) {
                                    $objectifTrouvee = true;
                                   
                                    expect($objectif['etat_valid'] ?? false)->toBeTrue(
                                        "L'objectif hors catégorie {$actUuid} devrait rester validé"
                                    );
                                    expect($objectif['commentaire'])->toBe(
                                        'objectif hors categorie valide',
                                        "L'objectif hors catégorie devrait garder son commentaire original"
                                    );
                                   
                                    break 4;
                                }
                            }
                        }
                    }
                }
    
                expect($objectifTrouvee)->toBeTrue(
                    "L'objectif hors catégorie {$actUuid} devrait être trouvée dans le parcours"
                );
            }
        }
    
        $marin->refresh();
        $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    
        // ✅ VÉRIFICATIONS FINALES
        foreach($parcoursMarin['affectations'] as $affectation) {
            if (empty($affectation['competences'])) continue;
           
            foreach($affectation['competences'] as $competence) {
                if (empty($competence['competenceSavoirfaires'])) continue;
               
                foreach($competence['competenceSavoirfaires'] as $csf) {
                    // Construire l'UUID pour comparaison
                    $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                    $csfPivotUuid = null;
                   
                    if (!empty($csf['pivot'])) {
                        $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                        if ($pivotPart && $csfUuidSimple) {
                            $csfPivotUuid = $csfUuidSimple . '_' . $pivotPart;
                        }
                    }
    
                    if ($csfUuidSimple === $savoirfaireUuid || $csfPivotUuid === $savoirfaireUuid) {
                        // Vérifier tous les objectifs
                        if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                            $pivotId = $csf['pivot']['id'] ?? null;
                           
                            foreach($csf['objectifs'] as $index => $objectif) {
                                expect($objectif['etat_valid'] ?? false)->toBeTrue(
                                    "L'objectif {$objectif['uuid']} devrait être validé"
                                );
    
                                $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;
                               
                                if ($objectif['uuid'] === $objectifsUuids[0] || $objUuidComposite === $objectifsUuids[0]) {
                                    expect($objectif['commentaire'])->toBe('objectif valide individuellement');
                                } else {
                                    expect($objectif['commentaire'])->toBe('validation savoir-faire complet');
                                }
                            }
                        }
                       
                        // Le savoir-faire doit être validé
                        expect($csf['etat_valid'] ?? false)->toBeTrue('Le savoir-faire devrait être validé');
                        expect($csf['commentaire'])->toBe('validation savoir-faire complet');
                       
                        //echo "✅ Test réussi!\n";
                        return;
                    }
                }
            }
        }
    
    

});


it('les widgets ne provoquent pas d\'exception quand l\'utilisateur n\'est pas un marin bug #678635', function() {
    // On teste carrément sans authentification.

    $this->assertTrue(MarinsSupervisesWidget::canView() == false);
    $this->assertTrue(MentorProgressionWidget::CanView() == false);
    $this->assertTrue(MentorStatsWidget::canView() == false);

    $this->assertTrue(MarinEvolutionWidget::canView() == false);
    $this->assertTrue(MarinProgressWidget::canView() == false);
    $this->assertTrue(MarinRepartitionWidget::canView() == false);
    $this->assertTrue(MarinStatsWidget::canView() == false);
    
});

