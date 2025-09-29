<?php
// filepath: Modules/FcmCentral/app/Providers/Filament/FcmcentralPanelProvider.php

namespace Modules\FcmCentral\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationBuilder;
use Filament\Facades\Filament;

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

use App\Filament\Pages\UserPreferences;
use Filament\Support\Assets\Theme;
use Modules\FcmCentral\Traits\FcmAccessControl;

// Import des Resources
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;

// Import des Pages
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;

class FcmcentralPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;
    use FcmAccessControl;

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
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $this->buildConditionalNavigation($builder);
            })
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
            ->userMenuItems($this->getConditionalUserMenuItems());

        return $panel;
    }

    /**
     * Construction de la navigation conditionnelle avec évaluation différée
     */
    private function buildConditionalNavigation(NavigationBuilder $builder): NavigationBuilder
{
    // Navigation avec groupes organisés
    return $builder->groups([
        // Groupe principal (sans nom)
        NavigationGroup::make()
            ->items([
                // Dashboard - toujours visible
                NavigationItem::make('Tableau de bord')
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.dashboard'))
                    ->url(fn (): string => Pages\Dashboard::getUrl()),
                
                // Mon livret FCM - visible seulement pour les marins FCM
                NavigationItem::make('Mon livret FCM')
                    ->icon('heroicon-o-document-text')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.mon-livret-de-fcm'))
                    ->url(fn (): string => MonLivretDeFcm::getUrl())
                    ->badge(fn () => $this->getMarinProgressBadge())
                    ->visible(fn (): bool => $this->canAccessMarinLivret()),
                
                // Mes marins FCM - visible seulement pour les mentors
                NavigationItem::make('Mes marins FCM')
                    ->icon('heroicon-o-users')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                    ->url(fn (): string => MarinResource::getUrl('index'))
                    ->badge(fn () => $this->getMentorMarinsCount())
                    ->visible(fn (): bool => $this->canAccessMentorFeatures()),
            ]),

        // Groupe Administration
        NavigationGroup::make('Administration')
            // ->icon('heroicon-o-cog-6-tooth')
            // ->collapsible()
            // ->collapsed()
            ->items([
                NavigationItem::make('Gestion des marins')
                    ->icon('heroicon-o-users')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                    ->url(fn (): string => MarinResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_marin') &&
                    ( self::canAccess('droits_admin') 
                    || self::canAccess('droits_gestion')
                    )
                ),
                
                NavigationItem::make('Gestion des cohortes')
                    ->icon('heroicon-o-user-group')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'cohorte'))
                    ->url(fn (): string => CohorteResource::getUrl('index'))
                    //->visible(fn (): bool => $this->canAccessAdminMenus()),
                    ->visible(fn (): bool => 
                    self::canAccess('attribuer_cohorte') &&
                    ( self::canAccess('droits_admin') 
                    || self::canAccess('droits_gestion')
                    )
                ),

                NavigationItem::make('Gestion des Notes')
                ->icon('heroicon-o-user-group')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'note'))
                ->url(fn (): string => NoteResource::getUrl('index'))
                //->visible(fn (): bool => $this->canAccessAdminMenus()),
                ->visible(fn (): bool => 
                self::canAccess('gerer_note') &&
                ( self::canAccess('droits_admin') 
                || self::canAccess('droits_gestion')
                )
            ),
            ])
            //->visible(fn (): bool => $this->canAccessAdminMenus())
            ,

        // Groupe Gestion des parcours
        NavigationGroup::make('Gestion des parcours')
            
            ->items([
                NavigationItem::make('Parcours figés')
                    ->icon('heroicon-o-lock-closed')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours-serialise'))
                    ->url(fn (): string => \Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('attribuer_parcours') &&
                    ( self::canAccess('droits_admin') 
                     || self::canAccess('droits_gestion')
                    )
                ),
                
                NavigationItem::make('Parcours')
                    ->icon('heroicon-o-map')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours') && !str_contains(request()->route()?->getName() ?? '', 'serialise'))
                    ->url(fn (): string => ParcoursResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_parcours') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),   
                
                NavigationItem::make('Fonctions')
                    ->icon('heroicon-o-briefcase')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'fonction'))
                    ->url(fn (): string => FonctionResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('attribuer_cohorte') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),   
                NavigationItem::make('Compétences')
                    ->icon('heroicon-o-star')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'competence'))
                    ->url(fn (): string => CompetenceResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_parcours') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),   
                
                NavigationItem::make('Savoir Faire')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'savoir-faire'))
                    ->url(fn (): string => SavoirFaireResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_parcours') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),   
                
                NavigationItem::make('Objectifs, Stages et EAD')
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'activite'))
                    ->url(fn (): string => ActiviteResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_parcours') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),  
                
                NavigationItem::make('Domaines')
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'domaine'))
                    ->url(fn (): string => DomaineResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    self::canAccess('gerer_parcours') &&
                    ( self::canAccess('droits_admin') 
                    // || self::canAccess('droits_gestion')
                    )
                ),
            ])
           
            ,

        // Groupe Outils
        NavigationGroup::make('Outils')
            
            ->items([
                NavigationItem::make('Recherche annuaire')
                    ->icon('heroicon-o-magnifying-glass')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.recherche-annuaire-page'))
                    ->url(fn (): string => RechercheAnnuairePage::getUrl())
                    ->visible(fn (): bool => $this->canAccessAdminMenus()),
            ])
            
            ,

            // Groupe Outils
        NavigationGroup::make('Administration ')
        
        ->items([
            NavigationItem::make('Configuration module')
                ->icon('heroicon-o-cog-6-tooth')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.configuration'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                ->visible(fn (): bool => self::canAccess('configuration_module')),
            
            NavigationItem::make('Gestion permissions')
                ->icon('heroicon-o-shield-check')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.permissions'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                ->visible(fn (): bool => self::canAccess('gerer_permissions')),
            
            NavigationItem::make('Logs système')
                ->icon('heroicon-o-document-text')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.logs'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                ->visible(fn (): bool => self::canAccess('voir_logs')),
            
            NavigationItem::make('Debug Permissions')
                ->icon('heroicon-o-bug-ant')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.debug-permissions'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL de debug
                ->visible(fn (): bool => self::canAccess('admin')),
        ])
    ]);
}


   

    /**
     * Widgets conditionnels selon les droits
     */
    private function getConditionalWidgets(): array
    {
        $widgets = [];

       
           
                $widgets = array_merge($widgets, [
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MentorStatsWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinsSupervisesWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MentorProgressionWidget::class,
                
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinProgressWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinStatsWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinEvolutionWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\MarinRepartitionWidget::class,
                    \Modules\FcmCentral\Filament\Fcmcentral\Widgets\TestMarinRolesPermissionsWidget::class,
                ]);
           
       

        return $widgets;
    }

    /**
     * Menu utilisateur conditionnel
     */
    private function getConditionalUserMenuItems(): array
    {
        $menuItems = [
            'profile' => MenuItem::make()
                ->label('Mon profil')
                ->icon('heroicon-m-user-circle'),
            
            'help' => MenuItem::make()
                ->label('Aide')
                ->icon('heroicon-m-question-mark-circle')
                ->url(fn () => url(config('skeletor.prefixe_instance') . '/docs/' . Filament::getCurrentPanel()->getId()) . '/', shouldOpenInNewTab: true),
            
            'apidoc' => MenuItem::make()
                ->label('API')
                ->icon('heroicon-m-cloud')
                ->url(fn () => url(route('l5-swagger.default.api')), shouldOpenInNewTab: true)
                ->visible(fn (): bool => $this->canAccessAdminMenus()),
        ];

        try {
            // Menu rapide pour les marins FCM
            $menuItems['mon-parcours'] = MenuItem::make()
                ->label('Mon parcours FCM')
                ->icon('heroicon-m-document-text')
                ->url(fn() => MonLivretDeFcm::getUrl())
                ->badge(fn() => $this->getMarinProgressBadge())
                ->visible(fn (): bool => $this->canAccessMarinLivret());

            // Menu rapide pour les mentors
            $menuItems['mes-marins'] = MenuItem::make()
                ->label('Mes marins supervisés')
                ->icon('heroicon-m-users')
                ->url(fn() => MarinResource::getUrl('index'))
                ->badge(fn() => $this->getMentorMarinsCount())
                //->badgeColor('success')
                ->visible(fn (): bool => $this->canAccessMentorFeatures());
                
            $menuItems['validations-attente'] = MenuItem::make()
                ->label('Validations en attente')
                ->icon('heroicon-m-clock')
                ->url(fn() => MarinResource::getUrl('index', ['tableFilters[en_attente][value]' => true]))
                ->badge(fn() => $this->getMentorPendingValidationsCount())
                //->badgeColor('warning')
                ->visible(fn(): bool => $this->canAccessMentorFeatures() && $this->getMentorPendingValidationsCount() > 0);

            // Menu spécifique aux admins
            $menuItems['admin-stats'] = MenuItem::make()
                ->label('Statistiques générales')
                ->icon('heroicon-m-chart-bar')
                ->url(fn() => Pages\Dashboard::getUrl())
                ->visible(fn (): bool => $this->canAccessAdminMenus());
            
            $menuItems['logout'] = MenuItem::make()
                ->label('Se déconnecter')
                ->icon('heroicon-m-arrow-right-on-rectangle');
                
        } catch (\Exception $e) {
            // En cas d'erreur, menu de base seulement
        }

        return $menuItems;
    }

    // ... reste des méthodes helper inchangées ...

    private function getMarinProgressBadge(): ?string
    {
        try {
            if (!$this->canAccessMarinLivret()) {
                return null;
            }

            $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
            if (!$marin || !$marin->complements_fcm) {
                return null;
            }

            $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
            if (!$parcours) return null;

            $totalActivites = 0;
            $activitesValidees = 0;
            
            foreach ($parcours['fonctions'] ?? [] as $fonction) {
                foreach ($fonction['competences'] ?? [] as $competence) {
                    foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                        foreach ($savoirfaire['activites'] ?? [] as $activite) {
                            $totalActivites++;
                            if (($activite['etat_valid'] ?? false) === true) {
                                $activitesValidees++;
                            }
                        }
                    }
                }
            }

            $progress = $totalActivites > 0 ? round(($activitesValidees / $totalActivites) * 100) : 0;
            return $progress . '%';
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getMentorMarinsCount(): ?string
    {
        try {
            if (!$this->canAccessMentorFeatures()) {
                return null;
            }

            $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
            if (!$currentMarin) {
                return null;
            }

            $count = \Modules\FcmCentral\Models\FcmMarin::where('mentor_id', $currentMarin->id)->count();
            return $count > 0 ? (string) $count : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getMentorPendingValidationsCount(): int
    {
        try {
            if (!$this->canAccessMentorFeatures()) {
                return 0;
            }

            $currentMarin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
            if (!$currentMarin) {
                return 0;
            }

            $marinIds = \Modules\FcmCentral\Models\FcmMarin::where('mentor_id', $currentMarin->id)
                ->pluck('rh_marin_id');

            $pendingCount = 0;
            foreach ($marinIds as $marinId) {
                $marin = \Modules\FcmCentral\Models\Marin::find($marinId);
                if ($marin && $marin->complements_fcm) {
                    $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;
                    if ($parcours) {
                        foreach ($parcours['fonctions'] ?? [] as $fonction) {
                            foreach ($fonction['competences'] ?? [] as $competence) {
                                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                                        $estProposee = !empty($activite['date_proposition']);
                                        $estValidee = ($activite['etat_valid'] ?? false) === true;
                                        $aDateValidation = !empty($activite['date_validation']);
                                        
                                        if ($estProposee && !$estValidee && !$aDateValidation) {
                                            $pendingCount++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $pendingCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

     // ========================================
        // MÉTHODES UTILITAIRES
        // ========================================
    
        /**
         * Vérification centralisée des permissions
         */
        public static function canAccess(string $permission): bool
        {
            $user = auth()->user();
            
            if (!$user) {
                return false;
            }
    
            // Ajouter le préfixe fcmcentral:: si nécessaire
            if (!str_starts_with($permission, 'fcmcentral::')) {
                $permission = 'fcmcentral::' . $permission;
            }
    
            return $user->can($permission);
        }


    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
