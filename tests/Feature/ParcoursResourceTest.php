<?php

use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\EditParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ListParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\AffectationsRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\CompetencesRelationManager;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers\SavoirfairesRelationManager;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\SavoirfaireType;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

beforeEach(function () {
    $this->admin = User::factory()->create();
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
    Gate::before(fn () => true);
});

// ============================================================
// CRÉATION
// ============================================================

describe('ParcoursResource - Création', function () {
    it('crée un parcours en base directement avec ses relations', function () {
        $parcoursType = ParcoursType::factory()->create();

        $parcours = Parcours::factory()->create([
            'code'             => 'TEST-001',
            'libelle_court'    => 'Parcours test',
            'libelle_long'     => 'Description complète du parcours test',
            'parcours_type_id' => $parcoursType->id,
        ]);

        $this->assertDatabaseHas('fcmcentral_parcours', [
            'code'          => 'TEST-001',
            'libelle_court' => 'Parcours test',
        ]);

        $this->assertNotNull($parcours->uuid);
        $this->assertEquals($parcoursType->id, $parcours->parcours_type_id);
    });

    it('un nouveau parcours a 0 version sérialisée', function () {
        $parcours = Parcours::factory()->create();

        $this->assertEquals(0, $parcours->getLatestSerializedVersion());
        $this->assertEquals(0, ParcoursSerialise::where('parcours_id', $parcours->id)->count());
    });

    it('affiche la liste des parcours dans Filament', function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');

        Livewire::actingAs($this->admin)
            ->test(ListParcours::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Parcours::all())
        ;
    });

    it('la liste est vide quand aucun parcours n\'existe', function () {
        Livewire::actingAs($this->admin)
            ->test(ListParcours::class)
            ->assertSuccessful()
            ->assertCountTableRecords(0)
        ;
    });

    it('génère un UserGeneratedEvent CREATION_PARCOURS_SERIALISE lors du figeage depuis la liste', function () {
        Event::fake();

        $parcours = Parcours::factory()->create(['code' => 'PAR-LISTE-TEST']);

        Livewire::actingAs($this->admin)
            ->test(ListParcours::class)
            ->callTableAction('serializeParcours', $parcours, data: [
                'date_de_debut' => Carbon::tomorrow()->format('Y-m-d'),
                'version'       => 1,
            ])
            ->assertHasNoActionErrors()
        ;

        Event::assertDispatched(
            UserGeneratedEvent::class,
            fn ($e) => $e->object_class == ObjectClasses::PARCOURS->value
                    && $e->object_uuid == $parcours->uuid
        );
    });
});

// ============================================================
// MODIFICATION
// ============================================================

describe('ParcoursResource - Modification', function () {
    beforeEach(function () {
        $this->parcoursType = ParcoursType::factory()->create();
        $this->parcours = Parcours::factory()->create([
            'code'             => 'CODE-ORIGINAL',
            'libelle_court'    => 'Libellé original',
            'libelle_long'     => 'Description originale',
            'parcours_type_id' => $this->parcoursType->id,
        ]);
    });

    it('affiche la page d\'édition avec les données existantes', function () {
        Livewire::actingAs($this->admin)
            ->test(EditParcours::class, ['record' => $this->parcours->id])
            ->assertSuccessful()
            ->assertFormSet([
                'code'          => 'CODE-ORIGINAL',
                'libelle_court' => 'Libellé original',
            ])
        ;
    });

    it('modifie les champs de base du parcours', function () {
        $newType = ParcoursType::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditParcours::class, ['record' => $this->parcours->id])
            ->fillForm([
                'code'             => 'CODE-MODIFIE',
                'libelle_court'    => 'Nouveau libellé court',
                'libelle_long'     => 'Nouveau libellé long',
                'parcours_type_id' => $newType->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
        ;

        $this->parcours->refresh();
        $this->assertEquals('CODE-MODIFIE', $this->parcours->code);
        $this->assertEquals('Nouveau libellé court', $this->parcours->libelle_court);
        $this->assertEquals($newType->id, $this->parcours->parcours_type_id);
    });

    it('refuse la modification avec un code vide', function () {
        Livewire::actingAs($this->admin)
            ->test(EditParcours::class, ['record' => $this->parcours->id])
            ->fillForm(['code' => ''])
            ->call('save')
            ->assertHasFormErrors(['code'])
        ;
    });

    it('conserve le uuid lors de la modification', function () {
        $uuidOriginal = $this->parcours->uuid;

        Livewire::actingAs($this->admin)
            ->test(EditParcours::class, ['record' => $this->parcours->id])
            ->fillForm(['libelle_court' => 'Modifié'])
            ->call('save')
            ->assertHasNoFormErrors()
        ;

        $this->parcours->refresh();
        $this->assertEquals($uuidOriginal, $this->parcours->uuid);
    });

    it('génère un UserGeneratedEvent CREATION_PARCOURS_SERIALISE depuis EditParcours', function () {
        Event::fake();

        $parcoursUuid = $this->parcours->uuid;

        Livewire::actingAs($this->admin)
            ->test(EditParcours::class, ['record' => $this->parcours->id])
            ->callAction('figer', data: [
                'version'       => 1,
                'date_de_debut' => Carbon::tomorrow()->format('Y-m-d'),
            ])
            ->assertHasNoFormErrors()
        ;

        Event::assertDispatched(
            UserGeneratedEvent::class,
            fn ($e) => $e->object_class == ObjectClasses::PARCOURS->value
                    && $e->object_uuid == $parcoursUuid
        );
    });
});

// ============================================================
// FIGER (SÉRIALISATION)
// ============================================================

describe('ParcoursResource - Figer', function () {
    beforeEach(function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $this->parcours = Parcours::first();
    });

    it('figer crée un ParcoursSerialise version 1', function () {
        $this->assertEquals(0, $this->parcours->getLatestSerializedVersion());

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $this->parcours->uuid,
            detail: ['version' => 1, 'date_de_debut' => Carbon::tomorrow()->format('Y-m-d')]
        ));

        $this->assertEquals(1, ParcoursSerialise::where('parcours_id', $this->parcours->id)->count());

        $v1 = ParcoursSerialise::where('parcours_id', $this->parcours->id)->first();
        $this->assertEquals(1, $v1->version);
        $this->assertNull($v1->date_fin);
        $this->assertEquals($this->parcours->id, $v1->parcours_id);
    });

    it('figer v2 clôture la v1 et reste la seule version active', function () {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $this->parcours->uuid,
            detail: ['version' => 1, 'date_de_debut' => Carbon::tomorrow()->format('Y-m-d')]
        ));

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $this->parcours->uuid,
            detail: ['version' => 2, 'date_de_debut' => Carbon::tomorrow()->addDay()->format('Y-m-d')]
        ));

        $this->assertEquals(2, ParcoursSerialise::where('parcours_id', $this->parcours->id)->count());

        $v1 = ParcoursSerialise::where('parcours_id', $this->parcours->id)->where('version', 1)->first();
        $v2 = ParcoursSerialise::where('parcours_id', $this->parcours->id)->where('version', 2)->first();

        $this->assertNotNull($v1->date_fin, 'La v1 doit être clôturée');
        $this->assertNull($v2->date_fin, 'La v2 doit rester active');
    });

    it('le ParcoursSerialise contient la structure complète du parcours (affectations)', function () {
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $this->parcours->uuid,
            detail: ['version' => 1, 'date_de_debut' => Carbon::tomorrow()->format('Y-m-d')]
        ));

        $serialise = ParcoursSerialise::where('parcours_id', $this->parcours->id)->first();
        $this->assertNotNull($serialise->parcours);

        $structure = is_string($serialise->parcours)
            ? json_decode($serialise->parcours, true)
            : $serialise->parcours;

        $this->assertIsArray($structure);
        $this->assertArrayHasKey('affectations', $structure);
    });

    it('getLatestSerializedVersion retourne la version la plus haute après 3 figeages', function () {
        foreach ([1, 2, 3] as $v) {
            event(new UserGeneratedEvent(
                event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
                user_uuid: $this->admin->uuid,
                object_class: ObjectClasses::PARCOURS,
                object_uuid: $this->parcours->uuid,
                detail: ['version' => $v, 'date_de_debut' => Carbon::tomorrow()->addDays($v)->format('Y-m-d')]
            ));
        }

        $this->parcours->refresh();
        $this->assertEquals(3, $this->parcours->getLatestSerializedVersion());
    });

    it('seule la dernière version n\'a pas de date_fin après plusieurs figeages', function () {
        foreach ([1, 2, 3] as $v) {
            event(new UserGeneratedEvent(
                event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
                user_uuid: $this->admin->uuid,
                object_class: ObjectClasses::PARCOURS,
                object_uuid: $this->parcours->uuid,
                detail: ['version' => $v, 'date_de_debut' => Carbon::tomorrow()->addDays($v)->format('Y-m-d')]
            ));
        }

        $versions = ParcoursSerialise::where('parcours_id', $this->parcours->id)->get();

        $actives  = $versions->whereNull('date_fin');
        $clotures = $versions->whereNotNull('date_fin');

        $this->assertEquals(1, $actives->count(), 'Une seule version active attendue');
        $this->assertEquals(2, $clotures->count(), 'Deux versions clôturées attendues');
        $this->assertEquals(3, $actives->first()->version, 'La version active doit être la v3');
    });
});

// ============================================================
// AFFECTATIONS RELATION MANAGER
// ============================================================

describe('ParcoursResource - AffectationsRelationManager', function () {
    beforeEach(function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $this->parcours = Parcours::first();
    });

    it('affiche les affectations du parcours', function () {
        $this->assertGreaterThan(0, $this->parcours->affectations->count());

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($this->parcours->affectations)
        ;
    });

    it('peut attacher une affectation existante au parcours', function () {
        $affectation = Affectation::factory()->create([
            'code'          => 'AFF-ATTACH',
            'libelle_court' => 'Affectation à attacher',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $affectation->id])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertTrue(
            $this->parcours->fresh()->affectations->contains('id', $affectation->id)
        );
    });

    it('peut créer une nouvelle affectation dans le parcours', function () {
        $countAvant = $this->parcours->affectations->count();

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('create', data: [
                'code'          => 'AFF-NEW',
                'libelle_court' => 'Nouvelle affectation',
                'libelle_long'  => 'Description longue',
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals($countAvant + 1, $this->parcours->fresh()->affectations->count());
    });

    it('peut modifier une affectation existante', function () {
        $affectation = $this->parcours->affectations->first();

        Livewire::actingAs($this->admin)
            ->test(AffectationsRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('edit', $affectation, data: [
                'code'          => 'AFF-MODIF',
                'libelle_court' => 'Libellé modifié',
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals('AFF-MODIF', $affectation->fresh()->code);
    });
});

// ============================================================
// COMPÉTENCES RELATION MANAGER
// ============================================================

describe('ParcoursResource - CompetencesRelationManager', function () {
    beforeEach(function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $this->parcours = Parcours::first();
    });

    it('affiche les compétences du parcours', function () {
        $competences = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)
            ->whereIn('affectation_id', $this->parcours->affectations->pluck('id'))
            ->get()
        ;

        $this->assertGreaterThan(0, $competences->count());

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($competences)
        ;
    });

    it('peut créer une compétence dans le parcours', function () {
        $affectation = $this->parcours->affectations->first();
        $countAvant  = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)->count();

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('creer_competence', data: [
                'affectation_id' => $affectation->id,
                'code'           => 'COMP-NEW',
                'libelle_court'  => 'Compétence test',
                'libelle_long'   => null,
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals(
            $countAvant + 1,
            Competence::where('parcours_type_id', $this->parcours->parcours_type_id)->count()
        );
    });

    it('peut modifier une compétence', function () {
        $competence = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)->first();

        Livewire::actingAs($this->admin)
            ->test(CompetencesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('edit', $competence, data: [
                'affectation_id' => $competence->affectation_id,
                'code'           => 'COMP-MODIF',
                'libelle_court'  => 'Compétence modifiée',
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals('COMP-MODIF', $competence->fresh()->code);
    });
});

// ============================================================
// SAVOIR-FAIRES RELATION MANAGER
// ============================================================

describe('ParcoursResource - SavoirfairesRelationManager', function () {
    beforeEach(function () {
        Artisan::call('fcmcentral:charge-parcours Modules/FcmCentral/tests/Feature/parcours_deasm.json');
        $this->parcours = Parcours::first();
    });

    it('affiche les savoir-faires du parcours', function () {
        $competenceIds = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)
            ->whereIn('affectation_id', $this->parcours->affectations->pluck('id'))
            ->pluck('id')
        ;

        $csfs = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)
            ->orderBy('ordre')
            ->take(10)
            ->get()
        ;

        $this->assertGreaterThan(0, $csfs->count());

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($csfs)
        ;
    });

    it('peut créer un savoir-faire simple sans objectifs', function () {
        $competence = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)->first();
        $type       = SavoirfaireType::where('code', 'S')->first();
        $countAvant = CompetenceSavoirfaire::where('competence_id', $competence->id)->count();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('create', data: [
                'competence_id'          => $competence->id,
                'nouvelle_code'          => 'SF-SIMPLE',
                'nouvelle_libelle_court' => 'Savoir-faire simple',
                'nouvelle_libelle_long'  => null,
                'savoirfaire_type_id'    => $type->id,
                'libelle_court'          => 'Contexte test',
                'heures'                 => 7,
                'jours'                  => null,
                'objectifs'              => [],
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals($countAvant + 1, CompetenceSavoirfaire::where('competence_id', $competence->id)->count());
    });

    it('peut créer un savoir-faire de type compagnonnage avec objectifs', function () {
        $competence = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)->first();
        $typeC      = SavoirfaireType::where('code', 'C')->first();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('create', data: [
                'competence_id'          => $competence->id,
                'nouvelle_code'          => 'SF-COMPAGNONNAGE',
                'nouvelle_libelle_court' => 'SF Compagnonnage',
                'savoirfaire_type_id'    => $typeC->id,
                'libelle_court'          => null,
                'heures'                 => 14,
                'jours'                  => null,
                'objectifs'              => [
                    ['code' => 'OBJ-A', 'libelle_court' => 'Objectif A', 'heures' => 7],
                    ['code' => 'OBJ-B', 'libelle_court' => 'Objectif B', 'heures' => 7],
                ],
            ])
            ->assertHasNoTableActionErrors()
        ;

        $csf = CompetenceSavoirfaire::whereHas('savoirfaire', fn ($q) => $q->where('code', 'SF-COMPAGNONNAGE'))->first();
        $this->assertNotNull($csf);
        $this->assertEquals(2, $csf->objectifs()->count());
    });

    it('peut modifier un savoir-faire existant', function () {
        $competenceIds = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)
            ->whereIn('affectation_id', $this->parcours->affectations->pluck('id'))
            ->pluck('id')
        ;

        $csf  = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->first();
        $type = SavoirfaireType::first();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('edit', $csf, data: [
                'competence_id'       => $csf->competence_id,
                'savoirfaire_id'      => $csf->savoirfaire_id,
                'sf_code'             => 'SF-MODIF',
                'sf_libelle_court'    => 'SF modifié',
                'sf_libelle_long'     => null,
                'savoirfaire_type_id' => $type->id,
                'libelle_court'       => 'Contexte modifié',
                'heures'              => 21,
                'jours'               => null,
                'objectifs'           => [],
            ])
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals('SF-MODIF', $csf->savoirfaire->fresh()->code);
        $this->assertEquals(21, $csf->fresh()->heures);
    });

    it('peut supprimer un savoir-faire', function () {
        $competenceIds = Competence::where('parcours_type_id', $this->parcours->parcours_type_id)
            ->whereIn('affectation_id', $this->parcours->affectations->pluck('id'))
            ->pluck('id')
        ;

        $csf        = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->first();
        $csfId      = $csf->id;
        $countAvant = CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->count();

        Livewire::actingAs($this->admin)
            ->test(SavoirfairesRelationManager::class, [
                'ownerRecord' => $this->parcours,
                'pageClass'   => EditParcours::class,
            ])
            ->callTableAction('delete', $csf)
            ->assertHasNoTableActionErrors()
        ;

        $this->assertEquals($countAvant - 1, CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)->count());
        $this->assertNull(CompetenceSavoirfaire::find($csfId));
    });
});


