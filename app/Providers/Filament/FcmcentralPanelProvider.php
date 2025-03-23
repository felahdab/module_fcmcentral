<?php

namespace Modules\FcmCentral\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\FontProviders\SpatieGoogleFontProvider;
use App\Filament\AvatarProviders\AnnudefAvatarProvider;
use App\Providers\Filament\Traits\UsesSkeletorPrefixAndMultitenancyTrait;

use App\Http\Middleware\InitializeTenancyByPath;
use App\Http\Middleware\SetTenantCookieMiddleware;
use App\Http\Middleware\SetTenantDefaultForRoutesMiddleware;

use Filament\Support\Assets\Theme;

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
            ->font('Inter', provider: SpatieGoogleFontProvider::class)
            ->defaultAvatarProvider(AnnudefAvatarProvider::class)
            ->discoverResources(in: module_path($this->module, 'app/Filament/Fcmcentral/Resources'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Resources")
            ->discoverPages(in: module_path($this->module, 'app/Filament/Fcmcentral/Pages'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Pages")
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'app/Filament/Fcmcentral/Widgets'), for: "$moduleNamespace\\Filament\\Fcmcentral\\Widgets")
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
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
            ->theme(Theme::make('fcmcentral-theme')->html(asset('css/fanlab/fcmcentral/module-fcmcentral.css')));
        return $panel;
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
