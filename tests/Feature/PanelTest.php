<?php

use function Pest\Livewire\livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{actingAs};
use Livewire\Livewire;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard;

use App\Models\User;
use App\Models\Setting;

use Modules\FcmCentral\Filament\Fcmcentral\Pages\Configuration;
use Modules\FcmCentral\Filament\Fcmcentral\Resources;
 
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

it('shows marins en FCM list', function() {
    Livewire::actingAs($this->admin)
        ->test(Resources\FcmMarinResource\Pages\ListFcmMarins::class)
        ->assertSee('Fcm Marins');
});