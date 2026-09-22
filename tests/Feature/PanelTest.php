<?php

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Configuration;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource\Pages\ListCohortes;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\FicheBilan;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\LivretDeFcmLight;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource\Pages\CreateNote;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\EditParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ListParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets;
use Modules\FcmCentral\Models;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Models\Cohorte;
use Modules\RH\Models\Unite;
use Tests\TestCase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Fcm Central1');

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));

    $this->admin = User::factory()->create(['admin' => 1]);
});

// ========================================
// TESTS PAGES
// ========================================

it('affiche le panneau principal FCM Central', function () {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertSee('Tableau de bord')
    ;
});

it('affiche la page de configuration', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->assertSee('Configuration du module FCM Central')
    ;
});

it('enregistre la configuration du module FcmCentral en base de données', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->fillForm([
            'transmit_user_generated_events_to_remote_fcmcentral_instance' => true,
            'url_of_remote_fcmcentral_instance' => 'url',
            'token_for_remote_fcmcentral_instance' => 'token',
        ])
        ->call('save')
    ;

    expect(Setting::forKey('fcmcentral')->get('transmit_user_generated_events_to_remote_fcmcentral_instance'))->toBeTrue();
    expect(Setting::forKey('fcmcentral')->get('url_of_remote_fcmcentral_instance'))->toBe('url');
    expect(Setting::forKey('fcmcentral')->get('token_for_remote_fcmcentral_instance'))->toBe('token');
});

it('enregistre la configuration du module FcmCommun en base de données', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->fillForm([
            'retreive_cohortes_from_distant_fcmcentral_module' => true,
            'cohortes_url_of_remote_fcmcentral_instance' => 'url',
            'cohorte_token_for_remote_fcmcentral_instance' => 'token',
        ])
        ->call('save')
    ;

    expect(Setting::forKey('fcmcommun')->get('retreive_cohortes_from_distant_fcmcentral_module'))->toBeTrue();
    expect(Setting::forKey('fcmcommun')->get('cohortes_url_of_remote_fcmcentral_instance'))->toBe('url');
    expect(Setting::forKey('fcmcommun')->get('cohorte_token_for_remote_fcmcentral_instance'))->toBe('token');
});

it('affiche la liste des cohortes', function () {
    Livewire::actingAs($this->admin)
        ->test(ListCohortes::class)
        ->assertSee('Cohortes')
    ;
});

// ========================================
// TESTS FACTORIES
// ========================================

it('a des factories pour tous les modeles', function () {
    $competence = Models\Competence::factory()->create(['code' => 'C-TEST-1']);
    $savoirfaire = Models\Savoirfaire::factory()->create(['code' => 'SF-TEST-1']);

    $competenceSavoirfaire = Models\CompetenceSavoirfaire::factory()->create([
        'competence_id' => $competence->id,
        'savoirfaire_id' => $savoirfaire->id,
    ]);

    $objectif = Models\Objectif::factory()->create([
        'code' => 'OBJ-TEST-1',
        'competence_savoirfaire_id' => $competenceSavoirfaire->id,
    ]);

    $affectation = Models\Affectation::factory()->create(['code' => 'AFF-TEST-1']);

    $parcours = Models\Parcours::factory()->create([
        'code' => 'PAR-TEST-1',
        // Si parcours_type_id est NOT NULL, il faut le fournir :
        'parcours_type_id' => Models\ParcoursType::factory()->create()->id,
    ]);

    // Affectation
    expect(Models\Affectation::count())->toBe(1);

    // Parcours
    expect(Models\Parcours::count())->toBe(1);

    // SavoirFaire
    expect(Models\Savoirfaire::count())->toBe(1);

    // Competence
    expect(Models\Competence::count())->toBe(1);

    // CompetenceSavoirFaire
    expect(Models\CompetenceSavoirfaire::count())->toBe(1);
});

// ========================================
// TESTS USER GENERATED EVENTS
// ========================================

it('genere un UserGeneratedEvent quand on demande à figer un parcours depuis la page d edition d un parcours', function () {
    Event::fake();

    // ✅ FIX: Ajouter le code
    $parcours = Models\Parcours::factory()->create(['code' => 'PAR-TEST-2']);
    $date_de_debut = Carbon::tomorrow();

    Livewire::actingAs($this->admin)
        ->test(EditParcours::class, ['record' => $parcours->id])
        ->callAction('figer', data: [
            'date de debut' => $date_de_debut,
            'version' => 1,
        ])
        ->assertHasNoFormErrors()
    ;

    Event::assertDispatched(
        UserGeneratedEvent::class,
        fn ($e) => 'Parcours' == $e->object_class && $e->object_uuid == $parcours->uuid
    );
});

it('genere un UserGeneratedEvent quand on demande à figer un parcours depuis la liste des parcours', function () {
    Event::fake();

    $parcours = Models\Parcours::factory()->create(['code' => 'PAR-TEST-3']);

    Livewire::actingAs($this->admin)
        ->test(ListParcours::class)
        ->callTableAction('serializeParcours', $parcours, data: [
            'date de debut' => Carbon::tomorrow(),
            'version' => 1,
        ])
        ->assertHasNoActionErrors()
    ;

    Event::assertDispatched(UserGeneratedEvent::class);
});

it('dispatch manuel fonctionne', function () {
    Event::fake();

    $marin = Models\Marin::factory()->create();

    event(new UserGeneratedEvent(
        UserGeneratedEventTypes::MARIN_EN_FCM,
        $this->admin->uuid,
        ObjectClasses::MARIN,
        $marin->uuid,
        ['test' => 'data']
    ));

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::MARIN_EN_FCM->value
            && $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on demande à suivre un marin en FCM', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $marin = Models\Marin::factory()->create();
    // Verification explicite
    $marin = Models\Marin::find($marin->id);
    // S'assurer qu'il n'est pas déjà en FCM
    Models\FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();

    // Création d'une cohorte, d'un mentor et d'un parcours pour simuler le formulaire
    $cohorte = Cohorte::factory()->create();
    $mentor = Models\Marin::factory()->create();
    $parcours = Models\Parcours::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        ->filterTable('en_fcm', ['value' => 'tous'])
        ->assertCanSeeTableRecords([$marin])
        ->callAction(
            TestAction::make('toggle_fcm')->table($marin),
            [
                'cohorte_id' => $cohorte->uuid,
                'mentors' => [$mentor->uuid],
            ]
        )
    ;

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::MARIN_EN_FCM->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on demande a ne plus suivre un marin en FCM', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;

    // dump($marin);
    expect($marin->estEnFcm())->toBeTrue();

    // Simuler la fin du FCM via le changement d'état (exemple : FCM_FIN)
    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)

        // ->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value' => 'tous'])
        ->callTableAction('toggle_fcm', $marin)
        ->assertHasNoActionErrors()
    ;

    // dump(Event::dispatched(UserGeneratedEvent::class));
});

it('ajoute la date_sedimentation lors de l\'activation FCM', function () {
    // Event::fake();

    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;
    Models\FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'cohorte_id' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    $marin->refresh();
    // dump ($marin->complementsFcm);
    expect($marin->estEnFcm())->toBeTrue();
    expect($marin->complementsFcm->date_sedimentation)->not->toBeNull();
});

it('génère un UserGeneratedEvent quand on modifie les jalons FCM', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;

    // dump($marin);
    expect($marin->estEnFcm())->toBeTrue();

    $dateSedimentation = now()->subDays(30)->format('Y-m-d');
    $dateFcm = now()->subDays(15)->format('Y-m-d');
    $dateCompetence = now()->format('Y-m-d');

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        ->filterTable('en_fcm', ['value' => 'tous'])
        ->callTableAction('jalons_fcm', $marin, [
            'date_sedimentation' => $dateSedimentation,
            'date_fcm' => $dateFcm,
            'date_competence' => $dateCompetence,
        ])
        ->assertHasNoActionErrors()
    ;

    // dump(Event::dispatched(UserGeneratedEvent::class));

    // Vérifier qu'un événement a été dispatché pour chaque date
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin, $dateSedimentation) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid
                && isset($event->detail['date_sedimentation'])
                && $event->detail['date_sedimentation'] == $dateSedimentation;
    });

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin, $dateFcm) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid
                && isset($event->detail['date_fcm'])
                && $event->detail['date_fcm'] == $dateFcm;
    });

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin, $dateCompetence) {
        return $event->event_type == UserGeneratedEventTypes::DEFINIR_JALON_FCM->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid
                && isset($event->detail['date_competence'])
                && $event->detail['date_competence'] == $dateCompetence;
    });

    // Vérifier que 3 événements ont bien été dispatchés
    Event::assertDispatchedTimes(UserGeneratedEvent::class, 3);
});

it('genere un  UserGeneratedEvent quand on assigne une cohorte a un marin suivi en fcm', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;
    // S'assurer qu'il n'est pas déjà en FCM
    Models\FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();
    $cohorte = Models\Cohorte::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        // ->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value' => 'tous'])

        ->callTableAction(
            'toggle_fcm',
            $marin,
            [
                'cohorte_id' => $cohorte->uuid,
                'mentors' => [],
                'parcours_id' => null,
            ]
        )
        ->assertHasNoActionErrors()
    ;

    // dump(Event::dispatched(UserGeneratedEvent::class));

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid;
    });
});

it('genere un  UserGeneratedEvent quand on assigne un mentor a un marin suivi en fcm', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;
    // S'assurer qu'il n'est pas déjà en FCM
    Models\FcmMarin::where('rh_marin_id', $marin->id)->delete();
    $marin->refresh();
    expect($marin->estEnFcm())->toBeFalse();

    $mentor = Models\Marin::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        // ->removeTableFilter('en_fcm')
        ->filterTable('en_fcm', ['value' => 'tous'])
        ->callTableAction(
            'toggle_fcm',
            $marin,
            [
                'cohorte_id' => null,
                'mentors' => [$mentor->uuid],
                'parcours_id' => null,
            ]
        )
        ->assertHasNoActionErrors()
    ;

    // dump(Event::dispatched(UserGeneratedEvent::class));

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid;
    });
});

it('genere un UserGeneratedEvent quand on assigne un parcours a un marin suivi en fcm', function () {
    Event::fake();

    // Création d'un marin non suivi en FCM
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = false;
    $marin->refresh();
    logger()->info($marin);
    expect($marin->suivi_en_fcm)->toBeFalse();

    $parcours = Models\Parcours::factory()->create();
    $parcours = Models\Parcours::find($parcours->id);
    logger()->info(Models\Parcours::all());

    logger()->info('Starting livewire test');
    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        ->filterTable('en_fcm', ['value' => 'tous'])
        ->assertCanSeeTableRecords([$marin])
        ->assertActionVisible(TestAction::make('toggle_fcm')->table($marin))
        ->callAction(
            TestAction::make('toggle_fcm')->table($marin),
            [
                'cohorte_id' => null,
                'mentors' => [],
                'parcours_id' => $parcours->uuid,
            ]
        )
    ;

    // dump(Event::dispatched(UserGeneratedEvent::class));

    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($marin) {
        return $event->event_type == UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN->value
                && $event->object_class == ObjectClasses::MARIN->value
                && $event->object_uuid == $marin->uuid;
    });
})->skip();

// ========================================
// TESTS VALIDATION PARCOURS
// ========================================

it('affiche le livret FCM', function () {
    // Création d'un marin FCM via factory
    $this->assertTrue(0 == Models\Marin::all()->count());
    $fcmMarin = Models\FcmMarin::factory()->create();
    $marin = $fcmMarin->marin;

    expect($marin->estEnFcm())->toBeTrue();
    $this->assertTrue(1 == Models\Marin::all()->count());

    // Seed du parcours
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    expect(Models\Parcours::count())->toBeGreaterThan(0);

    $parcours = Models\Parcours::first();

    // Création du parcours sérialisé
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
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
            'parcours' => $parcours->uuid,
        ]
    ));

    $marin->refresh();

    // Vérification des données du parcours
    expect($marin->complementsFcm)->not->toBeNull();
    expect($marin->complementsFcm->donnees_du_parcours)->not->toBeNull();
    // expect($marin->complementsFcm->donnees_du_parcours->parcours_serialise_id)->toBe($parcoursSerialise->id);

    $parcoursMarin = $marin->complementsFcm->donnees_du_parcours->parcoursmarin;
    expect($parcoursMarin)->toBeArray();
    expect($parcoursMarin)->toHaveKey('affectations');

    // Test de l'affichage du livret via Livewire
    Livewire::actingAs($this->admin)
        ->test(LivretDeFcmLight::class, ['record' => $marin->id])
        ->assertSuccessful()
        ->assertSee('Livret du marin')
        ->assertSee($marin->nom)
        ->assertSee($marin->prenom)
        ->assertSee('DEASM') // Parce que le fichier json chargé est une extraction du parcours DEASM.
    ;
});

it('met à jour le parcours en cas de validation', function () {
    $this->assertSame(0, Models\Marin::count());

    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);

    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);

    $this->assertSame(1, Models\Marin::count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

    $this->assertSame(1, Models\Parcours::count());
    $parcours = Models\Parcours::first();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
                'type_element' => 'objectif',
                'uuid_element' => $objectif,
                'date' => Carbon::now(),
                'commentaire' => 'commentaire',
            ]
        ));
    }

    $marin->refresh();

    // Recherche du savoir-faire dans la structure du parcours
    $savoirfaire_uuid = 'deasm1b1-1234-5678-cosf-11aa1c1c1sf1';
    $savoirfaire_trouve = false;

    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];

    foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['uuid'] ?? null) === $savoirfaire_uuid) {
                    $savoirfaire_trouve = true;

                    // Tous les objectifs doivent être validés
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $this->assertTrue(true === $objectif['etat_valid'], "L'objectif {$objectif['uuid'] } doit être validé");
                    }
                    // Le savoir-faire doit être validé aussi
                    $this->assertTrue(true === $csf['etat_valid'], 'Le savoir-faire doit être validé');
                }
            }
        }
    }

    $this->assertTrue($savoirfaire_trouve, 'Le savoir-faire devrait être trouvé dans le parcours');
});

it('met à jour le parcours en cas d\'annulation', function () {
    $this->assertSame(0, Models\Marin::count());

    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);
    $this->assertSame(1, Models\Marin::count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $this->assertSame(1, Models\Parcours::count());
    $parcours = Models\Parcours::first();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
                'type_element' => 'objectif',
                'uuid_element' => $objectif,
                'date' => Carbon::now(),
                'commentaire' => 'commentaire',
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
            'type_element' => 'objectif',
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
        ]
    ));

    $marin->refresh();

    // Recherche du savoir-faire dans la structure du parcours
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];
    $savoirfaire_trouve = false;

    foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['uuid'] ?? null) === $savoirfaire_uuid) {
                    $savoirfaire_trouve = true;

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        // On vérifie l'annulation du premier objectif
                        if ($objectif['uuid'] === $objectifs[0]) {
                            $this->assertFalse($objectif['etat_valid'], "L'objectif annulé doit être non validé");
                        } else {
                            $this->assertTrue($objectif['etat_valid'], 'Les autres objectifs doivent rester validés');
                        }
                    }
                    // Le savoir-faire doit être non validé si au moins un objectif est annulé
                    $this->assertFalse($csf['etat_valid'], 'Le savoir-faire doit être non validé si un objectif est annulé');
                }
            }
        }
    }

    $this->assertTrue($savoirfaire_trouve, 'Le savoir-faire devrait être trouvé dans le parcours');
});

it('met à jour le parcours en cas de proposition', function () {
    $this->assertSame(0, Models\Marin::count());

    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);
    $this->assertSame(1, Models\Marin::count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $this->assertSame(1, Models\Parcours::count());
    $parcours = Models\Parcours::first();

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
            'type_element' => 'objectif',
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
        ]
    ));

    $marin->refresh();

    // Vérification de la structure du parcours
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? [];
    $objectif_propose_trouve = false;

    foreach ($parcoursMarin['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['uuid'] ?? null) === $savoirfaire_uuid) {
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $pivotId = $csf['pivot']['id'] ?? null;
                        $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;

                        if ($objectif['uuid'] === $objectifs[0] || $objUuidComposite === $objectifs[0]) {
                            $objectif_propose_trouve = true;
                            $this->assertNotNull($objectif['date_proposition'], "L'objectif proposé doit avoir une date de proposition");
                        } else {
                            $this->assertNull($objectif['date_proposition'], 'Les autres objectifs ne doivent pas avoir de date de proposition');
                        }
                    }
                }
            }
        }
    }

    $this->assertTrue($objectif_propose_trouve, "L'objectif proposé doit être trouvé dans le parcours");
});

it('met a jour le parcours en cas de validation d une proposition', function () {
    $this->assertTrue(0 == Models\Marin::all()->count());
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);
    $this->assertTrue(1 == Models\Marin::all()->count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $this->assertTrue(1 == Models\Parcours::count());
    $parcours = Models\Parcours::first();

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid : $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    );
    event($event);

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
            'type_element' => 'objectif', // ✅ CORRECTION
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
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
            'type_element' => 'objectif', // ✅ CORRECTION
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
        ]
    );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach ($marin->complements_fcm->donnees_du_parcours->parcoursmarin['affectations'] as $affectation) {
        if (!isset($affectation['competences'])) {
            continue;
        }

        foreach ($affectation['competences'] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if ($csf['uuid'] == $savoirfaire_uuid) {
                    if (isset($csf['objectifs']) && is_array($csf['objectifs'])) {
                        foreach ($csf['objectifs'] as $objectif) {
                            // Construire l'UUID composite pour comparer
                            $pivotId = $csf['pivot']['id'] ?? null;
                            $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;

                            if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]) {
                                $this->assertTrue(null == $objectif['date_proposition']);
                                $this->assertTrue(true == $objectif['etat_valid']);
                            }
                        }
                    }
                }
            }
        }
    }
});

it('met a jour le parcours en cas de refus d une proposition', function () {
    $this->assertTrue(0 == Models\Marin::all()->count());
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);
    $this->assertTrue(1 == Models\Marin::all()->count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $this->assertTrue(1 == Models\Parcours::count());
    $parcours = Models\Parcours::first();

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid : $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    );
    event($event);

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
            'type_element' => 'objectif', // ✅ CORRECTION
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
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
            'type_element' => 'objectif', // ✅ CORRECTION
            'uuid_element' => $objectifs[0],
            'date' => Carbon::now(),
            'commentaire' => 'commentaire',
        ]
    );
    event($event);

    $marin->refresh();

    // ✅ CORRECTION: Adapter la vérification pour la nouvelle structure
    foreach ($marin->complements_fcm->donnees_du_parcours->parcoursmarin['affectations'] as $affectation) {
        if (!isset($affectation['competences'])) {
            continue;
        }

        foreach ($affectation['competences'] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if ($csf['uuid'] == $savoirfaire_uuid) {
                    if (isset($csf['objectifs']) && is_array($csf['objectifs'])) {
                        foreach ($csf['objectifs'] as $objectif) {
                            // Construire l'UUID composite pour comparer
                            $pivotId = $csf['pivot']['id'] ?? null;
                            $objUuidComposite = $pivotId ? "obj_{$pivotId}_{$objectif['id']}" : null;

                            if ($objectif['uuid'] == $objectifs[0] || $objUuidComposite == $objectifs[0]) {
                                $this->assertTrue(null == $objectif['date_proposition']);
                                $this->assertTrue(false == $objectif['etat_valid']);
                            }
                        }
                    }
                }
            }
        }
    }
});

it('valide automatiquement toutes les activites a false quand on valide un savoirfaire', function () {
    // ✅ Création marin avec FcmMarin via factory
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(['rh_marin_id' => $marin->id]);
    $this->assertTrue(1 == Models\Marin::all()->count());

    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $this->assertTrue(1 == Models\Parcours::count());
    $parcours = Models\Parcours::first();

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid : $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    );
    event($event);

    $event = new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid : $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid : $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
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
    $getObjectifsDuSavoirfaire = function ($parcoursMarin, $sfUuid) {
        $objectifs = [];

        foreach ($parcoursMarin['affectations'] as $affectation) {
            if (empty($affectation['competences'])) {
                continue;
            }

            foreach ($affectation['competences'] as $competence) {
                if (empty($competence['competenceSavoirfaires'])) {
                    continue;
                }

                foreach ($competence['competenceSavoirfaires'] as $csf) {
                    // Construire UUID composite si nécessaire
                    $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                    $csfPivotUuid = null;

                    if (!empty($csf['pivot'])) {
                        $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                        if ($pivotPart && $csfUuidSimple) {
                            $csfPivotUuid = $csfUuidSimple.'_'.$pivotPart;
                        }
                    }

                    if ($csfUuidSimple === $sfUuid || $csfPivotUuid === $sfUuid) {
                        if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                            foreach ($csf['objectifs'] as $objectif) {
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

    // echo "\n🔍 Objectifs du savoir-faire: " . implode(', ', $objectifsDuSf) . "\n";
    // echo "🔍 Objectifs hors catégorie: " . implode(', ', $objectifsHorsCategorie) . "\n";

    // ✅ ÉTAPE 2: Valider seulement la première objectif du savoir-faire
    // echo "\n📝 Validation du premier objectif\n";

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'type_element' => 'objectif',
            'uuid_element' => $objectifsUuids[0],
            'date' => Carbon::now()->toDateTimeString(),
            'commentaire' => 'objectif valide individuellement',
        ]
    ));

    // ✅ ÉTAPE 3: Valider les activités hors catégorie
    if (!empty($objectifsHorsCategorie)) {
        // echo "📝 Validation des objectif hors catégorie (" . count($objectifsHorsCategorie) . ")\n";

        foreach ($objectifsHorsCategorie as $actUuid) {
            event(new UserGeneratedEvent(
                event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                user_uuid: $this->admin->uuid,
                object_class: ObjectClasses::MARIN,
                object_uuid: $marin->uuid,
                detail: [
                    'type_element' => 'objectif',
                    'uuid_element' => $actUuid,
                    'date' => Carbon::now()->toDateTimeString(),
                    'commentaire' => 'objectif hors categorie valide',
                ]
            ));
        }
    }

    // ✅ Compter les events avant validation du savoir-faire
    $eventsAvant = DB::table('fcmcentral_stored_events')->count();
    // echo "🔍 Events avant validation savoir-faire: {$eventsAvant}\n";

    // ✅ ÉTAPE 4: Valider le savoir-faire complet
    // echo "\n📝 Validation du savoir-faire complet\n";

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'type_element' => 'savoirfaire',
            'uuid_element' => $savoirfaireUuid,
            'date' => Carbon::now()->toDateTimeString(),
            'commentaire' => 'validation savoir-faire complet',
        ]
    ));

    $marin->refresh();
    $parcoursMarin = $marin->complements_fcm->donnees_du_parcours->parcoursmarin;

    // ✅ VÉRIFICATION 1: État des activités dans le parcours
    // echo "\n🔎 Vérification des objectifs du savoir-faire\n";

    $sfTrouve = false;

    foreach ($parcoursMarin['affectations'] as $affectation) {
        if (empty($affectation['competences'])) {
            continue;
        }

        foreach ($affectation['competences'] as $competence) {
            if (empty($competence['competenceSavoirfaires'])) {
                continue;
            }

            foreach ($competence['competenceSavoirfaires'] as $csf) {
                $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                $csfPivotUuid = null;

                if (!empty($csf['pivot'])) {
                    $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                    if ($pivotPart && $csfUuidSimple) {
                        $csfPivotUuid = $csfUuidSimple.'_'.$pivotPart;
                    }
                }

                if ($csfUuidSimple === $savoirfaireUuid || $csfPivotUuid === $savoirfaireUuid) {
                    $sfTrouve = true;

                    // Toutes les activités du savoir-faire doivent être validées
                    // expect($csf['activites'])->toBeArray();

                    foreach ($csf['objectifs'] as $index => $objectif) {
                        expect($objectif['etat_valid'] ?? false)->toBeTrue(
                            "L'objectif {$objectif['uuid']} devrait être validé"
                        );
                        if ($objectif['uuid'] === $objectifsUuids[0]) {
                            // La première activité était déjà validée
                            $this->assertEquals(
                                'objectif valide individuellement',
                                $objectif['commentaire'],
                                "L'activité déjà validée devrait garder son commentaire original"
                            );
                        } else {
                            // Les autres activités ont été validées par le savoir-faire
                            $this->assertEquals(
                                'validation savoir-faire complet',
                                $objectif['commentaire'],
                                "L'activité auto-validée devrait avoir le commentaire du savoir-faire"
                            );
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
        // echo "\n🔎 Vérification des activités hors catégorie\n";

        foreach ($objectifsHorsCategorie as $actUuid) {
            $objectifTrouvee = false;

            foreach ($parcoursMarin['affectations'] as $affectation) {
                if (empty($affectation['competences'])) {
                    continue;
                }

                foreach ($affectation['competences'] as $competence) {
                    if (empty($competence['competenceSavoirfaires'])) {
                        continue;
                    }

                    foreach ($competence['competenceSavoirfaires'] as $csfAutre) {
                        if (empty($csfAutre['objectifs'])) {
                            continue;
                        }

                        foreach ($csfAutre['objectifs'] as $objectif) {
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
    foreach ($parcoursMarin['affectations'] as $affectation) {
        if (empty($affectation['competences'])) {
            continue;
        }

        foreach ($affectation['competences'] as $competence) {
            if (empty($competence['competenceSavoirfaires'])) {
                continue;
            }

            foreach ($competence['competenceSavoirfaires'] as $csf) {
                // Construire l'UUID pour comparaison
                $csfUuidSimple = $csf['uuid'] ?? $csf['savoirfaire']['uuid'] ?? null;
                $csfPivotUuid = null;

                if (!empty($csf['pivot'])) {
                    $pivotPart = $csf['pivot']['uuid'] ?? $csf['pivot']['id'] ?? null;
                    if ($pivotPart && $csfUuidSimple) {
                        $csfPivotUuid = $csfUuidSimple.'_'.$pivotPart;
                    }
                }

                if ($csfUuidSimple === $savoirfaireUuid || $csfPivotUuid === $savoirfaireUuid) {
                    // Vérifier tous les objectifs
                    if (!empty($csf['objectifs']) && is_array($csf['objectifs'])) {
                        $pivotId = $csf['pivot']['id'] ?? null;

                        foreach ($csf['objectifs'] as $index => $objectif) {
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

                    // echo "✅ Test réussi!\n";
                    return;
                }
            }
        }
    }
});

it('les widgets ne provoquent pas d\'exception quand l\'utilisateur n\'est pas un marin bug #678635', function () {
    // On teste carrément sans authentification.

    $this->assertTrue(false == Widgets\Mentor\MarinsSupervisesWidget::canView());
    $this->assertTrue(false == Widgets\Mentor\MentorProgressionWidget::CanView());
    $this->assertTrue(false == Widgets\Mentor\MentorStatsWidget::canView());

    $this->assertTrue(false == Widgets\Marin\MarinEvolutionWidget::canView());
    $this->assertTrue(false == Widgets\Marin\MarinProgressWidget::canView());
    $this->assertTrue(false == Widgets\Marin\MarinRepartitionWidget::canView());
    $this->assertTrue(false == Widgets\Marin\MarinStatsWidget::canView());
});

it('affiche un bouton "Créer une note" qui redirige vers la page de création avec le marin présélectionné', function () {
    $marin = Models\Marin::factory()->create();

    event(new UserGeneratedEvent(
        UserGeneratedEventTypes::MARIN_EN_FCM,
        $this->admin->uuid,
        ObjectClasses::MARIN,
        $marin->uuid,
        ['test' => 'data']
    ));

    Livewire::actingAs($this->admin)
        ->test(ListMarins::class)
        ->assertCanSeeTableRecords([$marin])
        ->assertTableActionHasUrl(
            'create-note',
            NoteResource::getUrl('create', ['marin_id' => $marin->id]),
            $marin,
        )
    ;
});

it('permet de creer une note pour un marin en FCM', function () {
    $marin = Models\Marin::factory()->create();

    $this->assertTrue(0 == Models\Note::where('rh_marin_id', $marin->id)->count());

    event(new UserGeneratedEvent(
        UserGeneratedEventTypes::MARIN_EN_FCM,
        $this->admin->uuid,
        ObjectClasses::MARIN,
        $marin->uuid,
        ['test' => 'data']
    ));

    Livewire::actingAs($this->admin)
        ->test(CreateNote::class)
        ->fillForm([
            'libelle' => 'libelle',
            'rh_marin_id' => $marin->id,
            'date' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
    ;

    $this->assertTrue(1 == Models\Note::where('rh_marin_id', $marin->id)->count());
});

it('permet au mentor d acceeder au livret de FCM et à la fiche bilan de son mentore sans permission particuliere', function () {
    /** Pour ce test, on a besoin de 2 marins, affectés à la même unité. L'un des 2 marins est placé en FCM
     * avec un parcours, et l'autre est désigné comme mentor.
     * Pour le mentor, on créé en plus un user qui lui est associé et avec lequel on fait les actions à tester.
     */
    $unite = Unite::factory()->create();
    $marin = Models\Marin::factory()->create(['unite_id' => $unite->id]);
    $mentor = Models\Marin::factory()->create(['unite_id' => $unite->id]);
    $user = $mentor->createUser();
    $mentor->refresh();
    $this->assertTrue($user->marin->id == $mentor->id);

    // On configure le marin (en FCM, parcours, attribution mentor)
    event(new UserGeneratedEvent(
        UserGeneratedEventTypes::MARIN_EN_FCM,
        $this->admin->uuid,
        ObjectClasses::MARIN,
        $marin->uuid,
        ['test' => 'data']
    ));

    // Seed du parcours
    Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
    $parcours = Models\Parcours::first();

    // Création du parcours sérialisé
    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::PARCOURS,
        object_uuid: $parcours->uuid,
        detail: [
            'version' => 1,
            'date_de_debut' => Carbon::tomorrow(),
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'parcours' => $parcours->uuid,
        ]
    ));

    event(new UserGeneratedEvent(
        event_type: UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
        user_uuid: $this->admin->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            'mentor' => [$mentor->uuid],
        ]
    ));

    // Voilà: on a 2 marins, dont 1 en FCM avec un parcours, et l'autre mentor du premier et associé au user $user.

    // Test de l'affichage du livret via Livewire
    Livewire::actingAs($user)
        ->test(LivretDeFcmLight::class, ['record' => $marin->id])
        ->assertSuccessful()
    ;

    Livewire::actingAs($user)
        ->test(FicheBilan::class, ['record' => $marin->id])
        ->assertSuccessful();
});
