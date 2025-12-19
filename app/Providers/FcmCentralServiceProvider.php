<?php

namespace Modules\FcmCentral\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;

use Modules\FcmCentral\Console\TestParcoursArchitecture;
use Modules\FcmCentral\Console\SeedTestData;
use Modules\FcmCentral\Console\TestAttributionParcoursAUser;

use App\Filament\PanelRegistry\DirectMenuItem;
use App\Filament\PanelRegistry\PreferedPageItem;
use App\Filament\PanelRegistry\ModuleDefinedMenusRegistry;
use App\Filament\PanelRegistry\ModuleDefinedPreferedPagesRegistry;

use Modules\FcmCentral\Models;
use Modules\FcmCentral\Policies;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages\ListParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;



class FcmCentralServiceProvider extends ServiceProvider
{

    
    protected string $moduleName = 'FcmCentral';

    protected string $moduleNameLower = 'fcmcentral';

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Models\Parcours::class => Policies\ParcoursPolicy::class,
        Models\ParcoursSerialise::class => Policies\ParcoursSerialisePolicy::class,
        Models\Affectation::class => Policies\AffectationPolicy::class,
        Models\Competence::class => Policies\CompetencePolicy::class,
        Models\Savoirfaire::class => Policies\SavoirfairePolicy::class,
        Models\Objectif::class => Policies\ObjectifPolicy::class,
        Models\Marin::class => Policies\MarinPolicy::class,
        Models\Domaine::class => Policies\DomainePolicy::class,
        Models\Cohorte::class => Policies\CohortePolicy::class,
        Models\Etat::class => Policies\EtatPolicy::class,
        Models\CompetenceSavoirfaire::class => Policies\CompetenceSavoirfairePolicy::class,
        Models\Note::class => Policies\NotePolicy::class,
        Models\ParcoursType::class => Policies\ParcoursTypePolicy::class,
        Models\SavoirfaireType::class => Policies\SavoirfaireTypePolicy::class,
        Models\TypeEtat::class => Policies\TypeEtatPolicy::class,
        Models\TypeNote::class => Policies\TypeNotePolicy::class

    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerDocumentation();
        $this->registerFilamentAssets();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->booting(function () {
            $this->registerPolicies();
        });

        $this->registerDirectMenuItems();
        $this->registerPreferedPagesItems();
    }

    public function registerDirectMenuItems()
    {
        app(ModuleDefinedMenusRegistry::class)->registerDirectMenuItems([
            DirectMenuItem::make()
                ->name('FCM central')
                ->children([
                    DirectMenuItem::make()
                        ->name('Gestion des marins')
                        ->url(fn() => ListMarins::getUrl(panel: "FCM Central"))
                        ->visible(fn() => ListMarins::canAccess()),
                    DirectMenuItem::make()
                        ->name("Mon livret de FCM")
                        ->url(fn() => MonLivretDeFcm::getUrl(panel: "FCM Central"))
                        ->visible(fn() => MonLivretDeFcm::canAccess())
                ])
        ]);
   
    }

    



    /// PAge Preferée
    public function registerPreferedPagesItems()
    {
        app(ModuleDefinedPreferedPagesRegistry::class)->registerPreferedPagesItems(
            [
            PreferedPageItem::make()
                ->name('FCM central: Gestion des marins')
                ->visible(fn() => auth()->check() && ListMarins::canAccess())
                ->routeName(fn() => ListMarins::getRouteName(panel: 'FCM Central')),
            PreferedPageItem::make()
                ->name('FCM central: Mon livret de FCM')
                ->visible(fn() => auth()->check() && MonLivretDeFcm::canAccess())
                ->routeName(fn() => MonLivretDeFcm::getRouteName(panel: 'FCM Central'))
            ]
        );
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return array<class-string, class-string>
     */
    public function policies()
    {
        return $this->policies;
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
                    TestParcoursArchitecture::class,
                    TestAttributionParcoursAUser::class,
                ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    protected function registerDocumentation()
    {
        $this->publishes([
            module_path($this->moduleName, 'resources/docs') => resource_path('docs/FCM Central')
        ], $this->moduleNameLower.'-module-documentation'
        );
    }
    
    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder','')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
       
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }

    private function registerFilamentAssets()
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    private function getAssets()
    {
        return [
            Css::make('module-fcmcentral', __DIR__.'/../../resources/dist/module-fcmcentral.css')->loadedOnRequest(),

        ];
    }

    private function getAssetPackageName()
    {
        return 'fanlab/fcmcentral';

    }
}
