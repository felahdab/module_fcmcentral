<?php

use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\PresentationSettings;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\PresentationWidget;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

pest()->group('Fcm Central');

// ─── Helpers ──────────────────────────────────────────────────────────────────

function creerUtilisateurAvecDroitDashboard(): User
{
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate([
        'name' => 'fcmcentral::dashboard',
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo($permission);

    return $user;
}

// ─────────────────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->admin = 1;
    $this->admin->save();
    Filament::setCurrentPanel(Filament::getPanel('FCM Central'));
});

// ============================================================
// ACCÈS
// ============================================================

describe('PresentationSettings - Accès', function () {

    it('charge la page sans erreur pour un admin', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->assertSuccessful();
    });

    it('refuse l\'accès à un utilisateur sans la permission fcmcentral::dashboard', function () {
        $user = User::factory()->create();

        expect(PresentationSettings::canAccess())->toBeFalse();

        $this->actingAs($user);
        expect(PresentationSettings::canAccess())->toBeFalse();
    });

    it('autorise l\'accès à un utilisateur avec la permission fcmcentral::dashboard', function () {
        $user = creerUtilisateurAvecDroitDashboard();
        $this->actingAs($user);

        expect(PresentationSettings::canAccess())->toBeTrue();
    });

});

// ============================================================
// ENREGISTREMENT DU TEXTE
// ============================================================

describe('PresentationSettings - Texte', function () {

    it('enregistre le texte de présentation en base', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm(['texte' => 'Bienvenue sur le module FCM Central'])
            ->call('save');

        expect(Setting::forKey('fcmcentral_presentation')->get('texte'))
            ->toBe('Bienvenue sur le module FCM Central');
    });

    it('précharge le texte existant à l\'ouverture de la page', function () {
        Setting::forKey('fcmcentral_presentation')->updateSetting(['texte' => 'Déjà là', 'logos' => []]);

        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->assertFormSet(['texte' => 'Déjà là']);
    });

    it('affiche une notification de succès après enregistrement', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm(['texte' => 'Test'])
            ->call('save')
            ->assertNotified();
    });

});

// ============================================================
// LOGOS — EMPLACEMENTS FIXES
// ============================================================

describe('PresentationSettings - Logos', function () {

    it('enregistre un logo rempli sur le premier emplacement', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm([
                'logos.0.image' => ['fake-key' => 'fcmcentral/dashboard/logos/fake.png'],
                'logos.0.libelle' => 'FPS Toulon',
                'logos.0.url' => 'https://example.com',
            ])
            ->call('save');

        $logos = Setting::forKey('fcmcentral_presentation')->get('logos');

        expect($logos)->toHaveCount(1)
            ->and($logos[0]['image'])->toBe('fcmcentral/dashboard/logos/fake.png')
            ->and($logos[0]['libelle'])->toBe('FPS Toulon')
            ->and($logos[0]['url'])->toBe('https://example.com');
    });

    it('ignore les emplacements vides et ne garde que les logos remplis', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm([
                'logos.0.image' => ['fake-key-1' => 'fcmcentral/dashboard/logos/fake1.png'],
                'logos.3.image' => ['fake-key-2' => 'fcmcentral/dashboard/logos/fake2.png'],
            ])
            ->call('save');

        $logos = Setting::forKey('fcmcentral_presentation')->get('logos');

        expect($logos)->toHaveCount(2)
            ->and(collect($logos)->pluck('image')->all())->toBe([
                'fcmcentral/dashboard/logos/fake1.png',
                'fcmcentral/dashboard/logos/fake2.png',
            ]);
    });

    it('n\'enregistre aucun logo si tous les emplacements sont vides', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm(['texte' => 'Sans logo'])
            ->call('save');

        expect(Setting::forKey('fcmcentral_presentation')->get('logos'))->toBe([]);
    });

    it('remplace intégralement la liste des logos à chaque enregistrement', function () {
        Setting::forKey('fcmcentral_presentation')->updateSetting([
            'texte' => '',
            'logos' => [
                ['image' => 'ancien.png', 'libelle' => 'Ancien', 'url' => null],
            ],
        ]);

        Livewire::actingAs($this->admin)
            ->test(PresentationSettings::class)
            ->fillForm([
                'logos.0.image' => ['fake-key' => 'nouveau.png'],
            ])
            ->call('save');

        $logos = Setting::forKey('fcmcentral_presentation')->get('logos');

        expect($logos)->toHaveCount(1)
            ->and($logos[0]['image'])->toBe('nouveau.png');
    });

});

// ============================================================
// WIDGET DE LECTURE SEULE (DASHBOARD)
// ============================================================

describe('PresentationWidget - Lecture seule', function () {

    it('affiche le texte et les logos enregistrés', function () {
        Setting::forKey('fcmcentral_presentation')->updateSetting([
            'texte' => 'Texte affiché',
            'logos' => [
                ['image' => 'fcmcentral/dashboard/logos/fake.png', 'libelle' => 'FPS Toulon', 'url' => null],
            ],
        ]);

        Livewire::actingAs($this->admin)
            ->test(PresentationWidget::class)
            ->assertSee('Texte affiché')
            ->assertSee('FPS Toulon');
    });

    it('affiche un message par défaut si aucune présentation n\'est configurée', function () {
        Livewire::actingAs($this->admin)
            ->test(PresentationWidget::class)
            ->assertSee('Aucune présentation configurée pour le moment.');
    });

    it('affiche le bouton Modifier pour un utilisateur avec la permission fcmcentral::dashboard', function () {
        $user = creerUtilisateurAvecDroitDashboard();

        Livewire::actingAs($user)
            ->test(PresentationWidget::class)
            ->assertSee('Modifier');
    });

    it('n\'affiche pas le bouton Modifier pour un utilisateur sans la permission', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PresentationWidget::class)
            ->assertDontSee('Modifier');
    });

});
