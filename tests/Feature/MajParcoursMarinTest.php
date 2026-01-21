<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\MarinParcours;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Objectif;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Models;
use Modules\FcmCentral\Database\Seeders\Parcours\DeasmMasterSeeder;
use Carbon\Carbon;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

pest()->group("Fcm Central1");

beforeEach(function () {
    $this->admin = \App\Models\User::factory()->create();
});

describe('Création Parcours Sérialisé - FcmCentral', function () {
   
    it('crée un parcours sérialisé version 1 sans anciennes versions', function () {
        $parcours = Parcours::factory()->create([
            'libelle_court' => 'Parcours Test',
            'code' => 'TEST001',
        ]);

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                'version' => 1,
                'date_de_debut' => now()->format('Y-m-d'),
            ]
        ));

        expect(ParcoursSerialise::where('parcours_id', $parcours->id)->count())->toBe(1);
       
        $serialise = ParcoursSerialise::where('parcours_id', $parcours->id)->first();
        expect($serialise->version)->toBe(1)
            ->and($serialise->date_fin)->toBeNull()
            ->and($serialise->parcours_id)->toBe($parcours->id);
    });

    it('met à jour parcoursserialise_id dans MarinParcours lors du changement de version', function () {
        $this->assertTrue(Models\Marin::all()->count() == 0);
       

        $fcmMarin1 = FcmMarin::factory()->create();
        $marin1 = $fcmMarin1->marin;
       
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin1->refresh();
        //dump ($marin->complementsFcm);
        expect($marin1->estEnFcm())->toBeTrue();
        



      
       
        $fcmMarin2 = FcmMarin::factory()->create();
        $marin2 = $fcmMarin2->marin;
        
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin2->refresh();
        //dump ($marin->complementsFcm);
        expect($marin2->estEnFcm())->toBeTrue();
       

        $this->assertTrue(Models\Marin::all()->count() == 2);
   
        // Seed du parcours
        $this->seed(DeasmMasterSeeder::class);
        expect(Models\Parcours::count())->toBeGreaterThan(0);
   
        $parcours = Models\Parcours::first();
   
        // ✅ Création du parcours sérialisé V1
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                "version" => 1,
                "date_de_debut" => Carbon::tomorrow()->format('Y-m-d'),
            ]
        ));
   
        // Vérification du parcours sérialisé V1
        $v1 = $parcours->parcoursSerialise()->latest('version')->first();
        expect($v1)->not->toBeNull();
        expect($v1->version)->toBe(1);
   
        // ✅ Attribuer le parcours au marin 1
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: [
                "parcours" => $parcours->uuid,
            ]
        ));
   
        // ✅ Attribuer le parcours au marin 2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: [
                "parcours" => $parcours->uuid,
            ]
        ));
   
        $marin1->refresh();
        $marin2->refresh();
   
        // ✅ Récupérer les MarinParcours créés
        $marinParcours1 = MarinParcours::where('fcmmarin_id', $fcmMarin1->id)
            ->where('parcoursserialise_id', $v1->id)
            ->first();
       
        $marinParcours2 = MarinParcours::where('fcmmarin_id', $fcmMarin2->id)
            ->where('parcoursserialise_id', $v1->id)
            ->first();
   
        expect($marinParcours1)->not->toBeNull();
        expect($marinParcours2)->not->toBeNull();
       
        // ✅ Vérifier qu'ils sont bien liés à V1
        expect($marinParcours1->parcoursserialise_id)->toBe($v1->id);
        expect($marinParcours2->parcoursserialise_id)->toBe($v1->id);
   
        // ✅ Créer la version 2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                'version' => 2,
                'date_de_debut' => now()->format('Y-m-d'),
            ]
        ));
   
        $v2 = ParcoursSerialise::where('parcours_id', $parcours->id)
            ->where('version', 2)
            ->first();
   
        expect($v2)->not->toBeNull();
   
        // ✅ Vérifier que les MarinParcours ont été mis à jour
        $marinParcours1->refresh();
        $marinParcours2->refresh();
   
        expect($marinParcours1->parcoursserialise_id)->toBe($v2->id)
            ->and($marinParcours2->parcoursserialise_id)->toBe($v2->id);
   
        // ✅ Vérifier via la relation
        expect($marinParcours1->parcours_serialise->id)->toBe($v2->id)
            ->and($marinParcours2->parcours_serialise->id)->toBe($v2->id);
   
        // ✅ Vérifier que v1 est clôturée
        $v1->refresh();
        expect($v1->date_fin)->not->toBeNull();
    })->group('Test', 'ParcoursSerialise', 'Listener');

    it('clôture toutes les anciennes versions (2, 6, 8 -> 10)', function () {
        $parcours = Parcours::factory()->create();
        $dateDebut = now()->format('Y-m-d');

        $v2 = ParcoursSerialise::factory()
            ->pourParcours($parcours)
            ->version(2)
            ->active()
            ->create();

        $v6 = ParcoursSerialise::factory()
            ->pourParcours($parcours)
            ->version(6)
            ->active()
            ->create();

        $v8 = ParcoursSerialise::factory()
            ->pourParcours($parcours)
            ->version(8)
            ->active()
            ->create();

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                'version' => 10,
                'date_de_debut' => $dateDebut,
            ]
        ));

        $v2->refresh();
        $v6->refresh();
        $v8->refresh();

        expect($v2->date_fin)->toBe($dateDebut)
            ->and($v6->date_fin)->toBe($dateDebut)
            ->and($v8->date_fin)->toBe($dateDebut);

        $v10 = ParcoursSerialise::where('parcours_id', $parcours->id)
            ->where('version', 10)
            ->first();
       
        expect($v10)->not->toBeNull()
            ->and($v10->date_fin)->toBeNull();
    });

    it('ne touche pas aux autres parcours lors de la mise à jour', function () {
        // ✅ Créer 2 parcours DISTINCTS
        $parcours1 = Parcours::factory()->create(['code' => 'PARC001']);
        $parcours2 = Parcours::factory()->create(['code' => 'PARC002']);

        // ✅ Créer 2 marins
        $fcmMarin1 = FcmMarin::factory()->create();
        $marin1 = $fcmMarin1->marin;
       
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin1->refresh();
        //dump ($marin->complementsFcm);
        expect($marin1->estEnFcm())->toBeTrue();
        



      
       
        $fcmMarin2 = FcmMarin::factory()->create();
        $marin2 = $fcmMarin2->marin;
        
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin2->refresh();
        //dump ($marin->complementsFcm);
        expect($marin2->estEnFcm())->toBeTrue();

        // ✅ Créer V1 pour parcours1
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours1->uuid,
            detail: [
                'version' => 1,
                'date_de_debut' => now()->subDays(10)->format('Y-m-d'),
            ]
        ));

        $v1_parcours1 = ParcoursSerialise::where('parcours_id', $parcours1->id)
            ->where('version', 1)
            ->first();

        // ✅ Créer V1 pour parcours2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours2->uuid,
            detail: [
                'version' => 1,
                'date_de_debut' => now()->subDays(10)->format('Y-m-d'),
            ]
        ));

        $v1_parcours2 = ParcoursSerialise::where('parcours_id', $parcours2->id)
            ->where('version', 1)
            ->first();

        // ✅ Attribuer parcours1 au marin1
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: ['parcours' => $parcours1->uuid]
        ));

        // ✅ Attribuer parcours2 au marin2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: ['parcours' => $parcours2->uuid]
        ));

        // ✅ Récupérer les MarinParcours
        $marinParcours1 = MarinParcours::where('fcmmarin_id', $fcmMarin1->id)
            ->where('parcoursserialise_id', $v1_parcours1->id)
            ->first();

        $marinParcours2 = MarinParcours::where('fcmmarin_id', $fcmMarin2->id)
            ->where('parcoursserialise_id', $v1_parcours2->id)
            ->first();

        expect($marinParcours1)->not->toBeNull();
        expect($marinParcours2)->not->toBeNull();

        // Vérification avant l'event
        expect($marinParcours1->parcoursserialise_id)->toBe($v1_parcours1->id)
            ->and($marinParcours2->parcoursserialise_id)->toBe($v1_parcours2->id);

        // ✅ Créer V2 SEULEMENT pour parcours1
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours1->uuid,
            detail: ['version' => 2, 'date_de_debut' => now()->format('Y-m-d')]
        ));

        // Vérifier parcours1
        $v1_parcours1->refresh();
        expect($v1_parcours1->date_fin)->not->toBeNull();

        $v2_parcours1 = ParcoursSerialise::where('parcours_id', $parcours1->id)
            ->where('version', 2)
            ->first();

        expect($v2_parcours1)->not->toBeNull();

        // ✅ Vérifier que MarinParcours1 a été mis à jour vers v2
        $marinParcours1->refresh();
        expect($marinParcours1->parcoursserialise_id)->toBe($v2_parcours1->id)
            ->and($marinParcours1->parcours_serialise->version)->toBe(2)
            ->and($marinParcours1->parcours_serialise->parcours_id)->toBe($parcours1->id);

        // ✅ Vérifier que parcours2 n'a pas changé
        $v1_parcours2->refresh();
        expect($v1_parcours2->date_fin)->toBeNull();

        // ✅ Vérifier que MarinParcours2 n'a PAS été touché
        $marinParcours2->refresh();
        expect($marinParcours2->parcoursserialise_id)->toBe($v1_parcours2->id)
            ->and($marinParcours2->parcours_serialise->version)->toBe(1)
            ->and($marinParcours2->parcours_serialise->parcours_id)->toBe($parcours2->id);

        // ✅ Vérification supplémentaire : compter les versions de chaque parcours
        $countV1 = ParcoursSerialise::where('parcours_id', $parcours1->id)->count();
        $countV2 = ParcoursSerialise::where('parcours_id', $parcours2->id)->count();

        expect($countV1)->toBe(2) // v1 + v2 pour parcours1
            ->and($countV2)->toBe(1); // v1 uniquement pour parcours2

    })->group('ParcoursSerialise', 'Listener', 'Isolation');

    it('gère plusieurs marins sur différentes versions du même parcours', function () {
        // ✅ Créer le parcours
        $parcours = Parcours::factory()->create(['code' => 'PARC_MULTI']);

        // ✅ Créer 3 marins
        $fcmMarin1 = FcmMarin::factory()->create();
        $marin1 = $fcmMarin1->marin;
       
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin1->refresh();
        //dump ($marin->complementsFcm);
        expect($marin1->estEnFcm())->toBeTrue();
        



      
       
        $fcmMarin2 = FcmMarin::factory()->create();
        $marin2 = $fcmMarin2->marin;
        
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin2->refresh();
        //dump ($marin->complementsFcm);
        expect($marin2->estEnFcm())->toBeTrue();




        $fcmMarin3 = FcmMarin::factory()->create();
        $marin3 = $fcmMarin3->marin;
        
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::MARIN_EN_FCM,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin3->uuid,
            detail: [
                "cohorte_id" => 1,
                "date_de_debut"=> Carbon::tomorrow(),
            ]
        ));
        $marin3->refresh();
        //dump ($marin->complementsFcm);
        expect($marin3->estEnFcm())->toBeTrue();

        

        // ✅ Créer V2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                'version' => 2,
                'date_de_debut' => now()->subDays(30)->format('Y-m-d'),
            ]
        ));

        $v2 = ParcoursSerialise::where('parcours_id', $parcours->id)
            ->where('version', 2)
            ->first();

        expect($v2)->not->toBeNull();

        // ✅ Attribuer V2 aux marins 1 et 2
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin1->uuid,
            detail: ['parcours' => $parcours->uuid]
        ));

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin2->uuid,
            detail: ['parcours' => $parcours->uuid]
        ));

        // ✅ Créer V5
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: [
                'version' => 5,
                'date_de_debut' => now()->subDays(15)->format('Y-m-d'),
            ]
        ));

        $v5 = ParcoursSerialise::where('parcours_id', $parcours->id)
            ->where('version', 5)
            ->first();

        expect($v5)->not->toBeNull();

        // ✅ Les marins 1 et 2 doivent être sur V5 maintenant
        // ✅ Attribuer V5 au marin 3
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $marin3->uuid,
            detail: ['parcours' => $parcours->uuid]
        ));

        // ✅ Récupérer les MarinParcours
        $marinParcours1 = MarinParcours::where('fcmmarin_id', $fcmMarin1->id)->first();
        $marinParcours2 = MarinParcours::where('fcmmarin_id', $fcmMarin2->id)->first();
        $marinParcours3 = MarinParcours::where('fcmmarin_id', $fcmMarin3->id)->first();

        // ✅ Vérification avant création de V7
        expect($marinParcours1->parcoursserialise_id)->toBe($v5->id)
            ->and($marinParcours2->parcoursserialise_id)->toBe($v5->id)
            ->and($marinParcours3->parcoursserialise_id)->toBe($v5->id);

        // ✅ Créer V7
        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: ['version' => 7, 'date_de_debut' => now()->format('Y-m-d')]
        ));

        $v7 = ParcoursSerialise::where('parcours_id', $parcours->id)
            ->where('version', 7)
            ->first();

        expect($v7)->not->toBeNull()
            ->and($v7->version)->toBe(7)
            ->and($v7->parcours_id)->toBe($parcours->id)
            ->and($v7->date_fin)->toBeNull();

        // ✅ Vérifier que TOUS les MarinParcours sont migrés vers v7
        $marinParcours1->refresh();
        $marinParcours2->refresh();
        $marinParcours3->refresh();

        expect($marinParcours1->parcoursserialise_id)->toBe($v7->id)
            ->and($marinParcours2->parcoursserialise_id)->toBe($v7->id)
            ->and($marinParcours3->parcoursserialise_id)->toBe($v7->id);

        // ✅ Vérifier via les relations
        expect($marinParcours1->parcours_serialise->version)->toBe(7)
            ->and($marinParcours2->parcours_serialise->version)->toBe(7)
            ->and($marinParcours3->parcours_serialise->version)->toBe(7);

        // ✅ Vérifier que v2 et v5 sont clôturées
        $v2->refresh();
        $v5->refresh();
        expect($v2->date_fin)->not->toBeNull()
            ->and($v5->date_fin)->not->toBeNull()
            ->and($v2->date_fin)->toBe(now()->format('Y-m-d'))
            ->and($v5->date_fin)->toBe(now()->format('Y-m-d'));

        // ✅ Vérifier qu'il n'y a que 3 versions pour ce parcours
        $totalVersions = ParcoursSerialise::where('parcours_id', $parcours->id)->count();
        expect($totalVersions)->toBe(3); // v2, v5, v7

        // ✅ Vérifier le nombre de MarinParcours liés à v7
        $countMarinsV7 = MarinParcours::where('parcoursserialise_id', $v7->id)->count();
        expect($countMarinsV7)->toBe(3);

        // ✅ Vérifier qu'aucun MarinParcours n'est resté sur v2 ou v5
        $countMarinsV2 = MarinParcours::where('parcoursserialise_id', $v2->id)->count();
        $countMarinsV5 = MarinParcours::where('parcoursserialise_id', $v5->id)->count();
        expect($countMarinsV2)->toBe(0)
            ->and($countMarinsV5)->toBe(0);

    })->group('FcmCentral', 'ParcoursSerialise', 'Listener', 'MultiVersion');

    it('utilise la date actuelle si date_de_debut non fournie', function () {
        $parcours = Parcours::factory()->create();

        event(new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
            user_uuid: $this->admin->uuid,
            object_class: ObjectClasses::PARCOURS,
            object_uuid: $parcours->uuid,
            detail: ['version' => 1]
        ));

        $serialise = ParcoursSerialise::where('parcours_id', $parcours->id)->first();
       
        expect($serialise->date_debut)->toBe(now()->format('Y-m-d'));
    })->group('FcmCentral', 'ParcoursSerialise', 'Listener', 'DateDefaut');

});
