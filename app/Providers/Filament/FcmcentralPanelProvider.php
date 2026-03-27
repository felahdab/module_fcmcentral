<?php

// filepath: Modules/FcmCentral/app/Providers/Filament/FcmcentralPanelProvider.php

namespace Modules\FcmCentral\Providers\Filament;

use App\Filament\AvatarProviders\AnnudefAvatarProvider;
use App\Filament\Pages\UserPreferences;
use App\Http\Middleware\InitializeTenancyByPath;
use App\Http\Middleware\SetTenantCookieMiddleware;
use App\Http\Middleware\SetTenantDefaultForRoutesMiddleware;
use App\Providers\Filament\Traits\UsesSkeletorPrefixAndMultitenancyTrait;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinEvolutionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinProgressWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinRepartitionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinStatsWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\DocumentationMentorWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MarinsSupervisesWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorProgressionWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorStatsWidget;
use Modules\FcmCentral\Filament\Fcmcentral\Widgets\TestMarinRolesPermissionsWidget;

class FcmcentralPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;

    private string $module = 'FcmCentral';

    public function panel(Panel $panel): Panel
    {
        $moduleNamespace = $this->getModuleNamespace();

        $panel
            ->id('FCM Central')
            ->path($this->prefix.'/fcmcentral')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->profile(UserPreferences::class)
            ->defaultAvatarProvider(AnnudefAvatarProvider::class)
            ->discoverResources(in: module_path($this->module, 'app/Filament/Fcmcentral/Resources'), for: "{$moduleNamespace}\\Filament\\Fcmcentral\\Resources")
            ->discoverPages(in: module_path($this->module, 'app/Filament/Fcmcentral/Pages'), for: "{$moduleNamespace}\\Filament\\Fcmcentral\\Pages")
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'app/Filament/Fcmcentral/Widgets'), for: "{$moduleNamespace}\\Filament\\Fcmcentral\\Widgets")
            ->widgets($this->getConditionalWidgets())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                InitializeTenancyByPath::class,
                SetTenantDefaultForRoutesMiddleware::class,
                SetTenantCookieMiddleware::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->topNavigation()
            ->theme(Theme::make('fcmcentral-theme')->html(asset('css/fanlab/fcmcentral/module-fcmcentral.css')))
            // Taille panel
            ->maxContentWidth(Width::Full)
            ->userMenuItems($this->getConditionalUserMenuItems())
        ;

        return $panel;
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }

    /**
     * Widgets conditionnels selon les droits.
     */
    private function getConditionalWidgets(): array
    {
        $widgets = [];

        return array_merge($widgets, [
            MentorStatsWidget::class,
            MarinsSupervisesWidget::class,
            MentorProgressionWidget::class,
            MarinProgressWidget::class,
            MarinStatsWidget::class,
            MarinEvolutionWidget::class,
            MarinRepartitionWidget::class,
            TestMarinRolesPermissionsWidget::class,
            DocumentationMentorWidget::class,
        ]);
    }

    /**
     * Menu utilisateur conditionnel (MEnu Avatar).
     */
    private function getConditionalUserMenuItems(): array
    {
        return [
            'help' => MenuItem::make()
                ->label('Aide')
                ->icon('heroicon-m-question-mark-circle')
                ->url(fn () => url(config('skeletor.prefixe_instance').'/docs/'.Filament::getCurrentOrDefaultPanel()->getId()).'/', shouldOpenInNewTab: true),

            'apidoc' => MenuItem::make()
                ->label('API')
                ->icon('heroicon-m-cloud')
                ->url(fn () => url(route('l5-swagger.default.api')), shouldOpenInNewTab: true),
        ];
    }
}
