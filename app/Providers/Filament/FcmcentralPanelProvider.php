<?php
// filepath: Modules/FcmCentral/app/Providers/Filament/FcmcentralPanelProvider.php

namespace Modules\FcmCentral\Providers\Filament;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\MenuItem;
use Filament\Facades\Filament;
use Filament\Support\Assets\Theme;
use Filament\FontProviders\SpatieGoogleFontProvider;

use App\Filament\AvatarProviders\AnnudefAvatarProvider;
use App\Providers\Filament\Traits\UsesSkeletorPrefixAndMultitenancyTrait;
use App\Http\Middleware\InitializeTenancyByPath;
use App\Http\Middleware\SetTenantCookieMiddleware;
use App\Http\Middleware\SetTenantDefaultForRoutesMiddleware;
use App\Filament\Pages\UserPreferences;

use Filament\Support\Enums\MaxWidth;

class FcmcentralPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;

    private string $module = "FcmCentral";

    public function panel(Panel $panel): Panel
    {
        $moduleNamespace = $this->getModuleNamespace();
        
        $panel
            ->id('FCM Central')
            ->path($this->prefix . '/fcmcentral')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->profile(UserPreferences::class)
            ->font('Inter', provider: SpatieGoogleFontProvider::class)
            ->defaultAvatarProvider(AnnudefAvatarProvider::class)
            ->discoverResources(in: module_path($this->module, 'app/Filament/Fcmcentral/Resources'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Resources")
            ->discoverPages(in: module_path($this->module, 'app/Filament/Fcmcentral/Pages'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Pages")
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'app/Filament/Fcmcentral/Widgets'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Widgets")
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
            ->maxContentWidth(MaxWidth ::Full) 
            ->userMenuItems($this->getConditionalUserMenuItems());

        return $panel;
    }

    /**
     * Widgets conditionnels selon les droits
     */
    private function getConditionalWidgets(): array
    {
        $widgets = [];
                $widgets = array_merge($widgets, [
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorStatsWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MarinsSupervisesWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Mentor\MentorProgressionWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinProgressWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinStatsWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinEvolutionWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\Marin\MarinRepartitionWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\TestMarinRolesPermissionsWidget::class,
                ]);
        return $widgets;
    }

    /**
     * Menu utilisateur conditionnel (MEnu Avatar)
     */
    private function getConditionalUserMenuItems(): array
    {
        $menuItems = [
            'help' => MenuItem::make()
                ->label('Aide')
                ->icon('heroicon-m-question-mark-circle')
                ->url(fn () => url(config('skeletor.prefixe_instance') . '/docs/' . Filament::getCurrentPanel()->getId()) . '/', shouldOpenInNewTab: true),
            
            'apidoc' => MenuItem::make()
                ->label('API')
                ->icon('heroicon-m-cloud')
                ->url(fn () => url(route('l5-swagger.default.api')), shouldOpenInNewTab: true),
        ];

        return $menuItems;
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
