<?php

use function Pest\Livewire\livewire;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

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

it('displays the FCM Central panel', function() {
    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertSee('Tableau de bord');
});

it('displays the configuration page', function() {
    Livewire::actingAs($this->admin)
        ->test(Configuration::class)
        ->assertSee('Configuration du module FCM Central');
});

it('saves configuration settings to fcmcentral settings', function() {
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

it('saves configuration settings to fcmcommun settings', function() {
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

it('shows cohorte list', function() {
    Livewire::actingAs($this->admin)
        ->test(Resources\CohorteResource\Pages\ListCohortes::class)
        ->assertSee('Cohortes');
});

it('has factories for all models', function(){
    Models\Activite::factory()->create();
    $this->assertTrue(Models\Activite::all()->count() == 1);

    Models\Competence::factory()->create();
    $this->assertTrue(Models\Competence::all()->count() == 1);

    Models\Fonction::factory()->create();
    $this->assertTrue(Models\Fonction::all()->count() == 1);

    Models\Parcours::factory()->create();
    $this->assertTrue(Models\Parcours::all()->count() == 1);

    Models\Savoirfaire::factory()->create();
    $this->assertTrue(Models\Savoirfaire::all()->count() == 1);

});

it('generated a UserGeneratedEvent when user wants to fige a parcours from the edit page', function(){
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

it('generated a UserGeneratedEvent when user wants to fige a parcours from the list page', function(){
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

it('generates a UserGeneratedEvent when user wants to suivre en FCM un marin', function(){
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

it('generates a UserGeneratedEvent when user wants to ne plus suivre en FCM un marin', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $this->assertTrue(Models\Marin::all()->count() == 1);

    $marin->suivi_en_fcm = true;
    $marin = Models\Marin::find($marin->id);
    
    Livewire::actingAs($this->admin)
        ->test(Resources\MarinResource\Pages\ListMarins::class)
        //->filterTable('en_fcm')
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

it('generates a UserGeneratedEvent when user wants to assign cohorte', function(){
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

it('generates a UserGeneratedEvent when user wants to assign a mentor', function(){
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

it('generates a UserGeneratedEvent when user wants to assign a parcours', function(){
    Event::fake();

    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);
    
    $parcours = Models\Parcours::factory()->create();
    $fonction = Models\Fonction::factory()->create();
    $parcours->fonctions()->attach($fonction);

    $competence = Models\Competence::factory()->create();
    $fonction->competences()->attach($competence);

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

it('displays the livret FCM ', function(){
    $this->assertTrue(Models\Marin::all()->count() == 0);
    $marin = Models\Marin::factory()->create();
    $marin = Models\Marin::find($marin->id);
    $marin->suivi_en_fcm = true;
    Models\FcmMarin::create(["rh_marin_id" => $marin->id]);
    $this->assertTrue(Models\Marin::all()->count() == 1);
    
    $parcours = Models\Parcours::factory()->create();
    $fonction = Models\Fonction::factory()->create();
    $parcours->fonctions()->attach($fonction);

    $competence = Models\Competence::factory()->create();
    $fonction->competences()->attach($competence);

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