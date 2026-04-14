<?php

namespace Modules\FcmCentral\Providers;

use App\Filament\PanelRegistry\DirectMenuItem;
use App\Filament\PanelRegistry\ModuleDefinedMenusRegistry;
use App\Filament\PanelRegistry\ModuleDefinedPreferedPagesRegistry;
use App\Filament\PanelRegistry\PreferedPageItem;
use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\FcmCentral\Console\ChargeParcours;
use Modules\FcmCentral\Console\SauvegardeParcours;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\ListMarins;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\Cohorte;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Domaine;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;
use Modules\FcmCentral\Models\TypeEtat;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Policies;
use Modules\FcmCentral\Policies\AffectationPolicy;
use Modules\FcmCentral\Policies\CohortePolicy;
use Modules\FcmCentral\Policies\CompetencePolicy;
use Modules\FcmCentral\Policies\CompetenceSavoirfairePolicy;
use Modules\FcmCentral\Policies\DomainePolicy;
use Modules\FcmCentral\Policies\EtatPolicy;
use Modules\FcmCentral\Policies\MarinPolicy;
use Modules\FcmCentral\Policies\NotePolicy;
use Modules\FcmCentral\Policies\ObjectifPolicy;
use Modules\FcmCentral\Policies\ParcoursPolicy;
use Modules\FcmCentral\Policies\ParcoursSerialisePolicy;
use Modules\FcmCentral\Policies\ParcoursTypePolicy;
use Modules\FcmCentral\Policies\SavoirfairePolicy;
use Modules\FcmCentral\Policies\SavoirfaireTypePolicy;
use Modules\FcmCentral\Policies\TypeEtatPolicy;
use Modules\FcmCentral\Policies\TypeNotePolicy;

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
        Affectation::class => AffectationPolicy::class,
        Cohorte::class => CohortePolicy::class,
        Competence::class => CompetencePolicy::class,
        CompetenceSavoirfaire::class => CompetenceSavoirfairePolicy::class,
        Domaine::class => DomainePolicy::class,
        Etat::class => EtatPolicy::class, // GEL

        Marin::class => MarinPolicy::class,
        Note::class => NotePolicy::class,
        Objectif::class => ObjectifPolicy::class,
        Parcours::class => ParcoursPolicy::class,
        ParcoursSerialise::class => ParcoursSerialisePolicy::class,
        ParcoursType::class => ParcoursTypePolicy::class,
        Savoirfaire::class => SavoirfairePolicy::class,
        SavoirfaireType::class => SavoirfaireTypePolicy::class,
        TypeNote::class => TypeNotePolicy::class,
        TypeEtat::class => TypeEtatPolicy::class, // TYPE GEL
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
                        ->url(fn () => ListMarins::getUrl(panel: 'FCM Central'))
                        ->visible(fn () => ListMarins::canAccess()),
                    DirectMenuItem::make()
                        ->name('Mon livret de FCM')
                        ->url(fn () => MonLivretDeFcm::getUrl(panel: 'FCM Central'))
                        ->visible(function () {
                            // On n'affiche cette page que si l'utilisateur actuel a une fiche de Marin et qu'il a un parcours de FCM
                            $marin = Marin::fromCurrentUser();
                            if (null == $marin) {
                                return false;
                            }

                            return null != $marin->complements_fcm?->donnees_du_parcours;
                        }),
                ]),
        ]);
    }

    // / PAge Preferée
    public function registerPreferedPagesItems()
    {
        app(ModuleDefinedPreferedPagesRegistry::class)->registerPreferedPagesItems(
            [
                PreferedPageItem::make()
                    ->name('FCM central: Gestion des marins')
                    ->visible(fn () => ListMarins::canAccess())
                    ->routeName(fn () => ListMarins::getRouteName(Filament::getPanel('FCM Central'))),
                PreferedPageItem::make()
                    ->name('FCM central: Mon livret de FCM')
                    ->visible(function () {
                        // On n'affiche cette page que si l'utilisateur actuel a une fiche de Marin et qu'il a un parcours de FCM
                        $marin = Marin::fromCurrentUser();
                        if (null == $marin) {
                            return false;
                        }

                        return null != $marin->complements_fcm?->donnees_du_parcours;
                    })
                    ->routeName(fn () => MonLivretDeFcm::getRouteName(Filament::getPanel('FCM Central'))),
            ]
        );
    }

    /**
     * Register the application's policies.
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

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder', '')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register commands in the format of Command::class.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            ChargeParcours::class,
            SauvegardeParcours::class,
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

    protected function registerDocumentation()
    {
        $this->publishes([
            module_path($this->moduleName, 'resources/docs') => base_path('resources/docs/docs/FCM Central'),
        ], 'doc');
        $this->publishes([
            module_path($this->moduleName, 'resources/pdfs') => public_path('pdfs/fcmcentral'),
        ], 'doc');
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
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
