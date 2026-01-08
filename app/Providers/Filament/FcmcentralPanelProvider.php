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

use Modules\FcmCentral\Traits\FcmPermissionTrait;
use Modules\FcmCentral\Traits\FilamentPermissionTrait;



// Import des Resources
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\AffectationResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceSavoirfaireResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirfaireResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ObjectifResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\NoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeNoteResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\EtatResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\TypeEtatResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DomaineResource;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursTypeResource;


// Import des Pages
use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\Configuration;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;
use Modules\FcmCentral\Models\ParcoursType;

class FcmcentralPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;
    use FcmPermissionTrait;
    use FilamentPermissionTrait;



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
            ->userMenuItems($this->getConditionalUserMenuItems())
            ;

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
                NavigationItem::make('Tableau de bord ')
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.dashboard'))
                    ->url(fn (): string => Pages\Dashboard::getUrl()),
                
                // Mon livret FCM - visible seulement pour les marins FCM
                NavigationItem::make('Mon livret FCM')
                    ->icon('heroicon-o-document-text')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.mon-livret-de-fcm'))
                    ->url(fn (): string => MonLivretDeFcm::getUrl())
                    ->badge(fn () => $this->getMarinProgressBadge())
                    ->visible(fn (): bool => $this->canAccessModuleWithPermissions('permission.marin')),
                
                // Mes marins FCM - visible seulement pour les mentors
                NavigationItem::make('Mes marins FCM')
                    ->icon('heroicon-o-users')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                    ->url(fn (): string => MarinResource::getUrl('index'))
                    ->badge(fn () => $this->getMentorMarinsCount())
                    ->visible(fn (): bool => $this->canAccessModuleWithPermissions('permission.mentor',['permission.bcf'])),
            ]),

        // Groupe Administration
        NavigationGroup::make('Gestion')
            // ->icon('heroicon-o-cog-6-tooth')
            // ->collapsible()
            // ->collapsed()
            ->items([
                NavigationItem::make('Gestion des marins')
                    ->icon('heroicon-o-users')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                    ->url(fn (): string => MarinResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['marin.index']))
                    ,
                
                NavigationItem::make('Gestion des cohortes')
                    ->icon('heroicon-o-user-group')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'cohorte'))
                    ->url(fn (): string => CohorteResource::getUrl('index'))
                    //->visible(fn (): bool => $this->canAccessAdminMenus()),
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['cohorte.index']))
                    ,

                NavigationItem::make('Gestion des Notes')
                ->icon('heroicon-o-user-group')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'note') && !str_contains(request()->route()?->getName() ?? '', 'type'))
                ->url(fn (): string => NoteResource::getUrl('index'))
                //->visible(fn (): bool => $this->canAccessAdminMenus()),
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['note.index']))
                ,
                NavigationItem::make('Gestion des Types Notes')
                ->icon('heroicon-o-user-group')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'type-notes'))
                ->url(fn (): string => TypeNoteResource::getUrl('index'))
                //->visible(fn (): bool => $this->canAccessAdminMenus()),
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['typeNote.index']))
                ,

                NavigationItem::make('Gestion des Etats')
                ->icon('heroicon-o-user-group')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'etat') && !str_contains(request()->route()?->getName() ?? '', 'type'))
                ->url(fn (): string => EtatResource::getUrl('index'))
                //->visible(fn (): bool => $this->canAccessAdminMenus()),
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['etat.index']))
                ,
                NavigationItem::make('Gestion des Types Etats')
                ->icon('heroicon-o-user-group')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'type-etats'))
                ->url(fn (): string => TypeEtatResource::getUrl('index'))
                //->visible(fn (): bool => $this->canAccessAdminMenus()),
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['typeEtat.index']))
                ,

            ])
            //->visible(fn (): bool => $this->canAccessAdminMenus())
            ,

        // Groupe Gestion des parcours
        NavigationGroup::make('Parcours')
            
            ->items([
                NavigationItem::make('Parcours figés')
                    ->icon('heroicon-o-lock-closed')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours-serialise'))
                    ->url(fn (): string => \Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['parcours.index']))
                    ,
                
                NavigationItem::make('Parcours')
                    ->icon('heroicon-o-map')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours') && !str_contains(request()->route()?->getName() ?? '', 'serialise'))
                    ->url(fn (): string => ParcoursResource::getUrl('index'))
                    
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['parcoursSerialise.index']))
                    ,

                NavigationItem::make('Affectation')
                ->icon('heroicon-o-briefcase')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'affectation') )
                ->url(fn (): string => AffectationResource::getUrl('index'))
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['affectation.index']))
                ,
                
                NavigationItem::make('Competences Savoirfaire')
                    ->icon('heroicon-o-briefcase')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'competence-savoirfaire') )
                    ->url(fn (): string => CompetenceSavoirfaireResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['competenceSavoirfaire.index']))
                    ,
                
                NavigationItem::make('Objectifs')
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'objectif'))
                    ->url(fn (): string => ObjectifResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['objectif.index']))
                    ,
                
                
            ])
           
            ,

            // Groupe Gestion des parcours
            NavigationGroup::make('Catalogue')  
            ->items([

                NavigationItem::make('Compétences')
                    ->icon('heroicon-o-star')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'competence') && !str_contains(request()->route()?->getName() ?? '', '-savoirfaire') )
                    ->url(fn (): string => CompetenceResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['competence.index']))
                    ,
                
                NavigationItem::make('Savoir Faire')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'savoir-faire'))
                    ->url(fn (): string => SavoirfaireResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['savoirfaire.index']))
                    ,
                
                

                NavigationItem::make('Domaines')
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'domaine'))
                    ->url(fn (): string => DomaineResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['domaine.index']))
                    ,

                NavigationItem::make('Type ')
                    ->icon('heroicon-o-rectangle-stack')
                    ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours-types'))
                    ->url(fn (): string => ParcoursTypeResource::getUrl('index'))
                    ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['parcoursType.index']))
                    ,
            ])
           
            ,


        

            // Groupe Outils
        NavigationGroup::make('Administration')
        
        ->items([
            NavigationItem::make('Configuration module')
                ->icon('heroicon-o-cog-6-tooth')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.configuration'))
                ->url(fn (): string => Configuration::getUrl()) // TODO: Remplacer par votre URL
                ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['permission.bcf','admin']))
                    ,
            
            NavigationItem::make('Gestion permissions')
                ->icon('heroicon-o-shield-check')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.permissions'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['permission.bcf','admin']))
                    ,
            
            NavigationItem::make('Logs système')
                ->icon('heroicon-o-document-text')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.logs'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                ->visible(fn (): bool => 
                    $this->canAccessAnyPermission(['permission.bcf','admin']))
                    ,
            
            NavigationItem::make('Debug Permissions')
                ->icon('heroicon-o-bug-ant')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.debug-permissions'))
                ->url(fn (): string => '#') // TODO: Remplacer par votre URL de debug
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['permission.bcf','admin']))
                ,
        ])
    ]);
}


   

    

    /**
     * Menu utilisateur conditionnel (MEnu Avatar)
     */
    private function getConditionalUserMenuItems(): array
    {
        $menuItems = [
            

            'mon-parcours' => MenuItem::make()
                ->label('Mon parcours FCM')
                ->icon('heroicon-m-document-text')
                ->url(fn() => MonLivretDeFcm::getUrl())
                
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['permission.mentor','permission.bcf']))
                ,
            
            'help' => MenuItem::make()
                ->label('Aide')
                ->icon('heroicon-m-question-mark-circle')
                ->url(fn () => url(config('skeletor.prefixe_instance') . '/docs/' . Filament::getCurrentPanel()->getId()) . '/', shouldOpenInNewTab: true),
            
            'apidoc' => MenuItem::make()
                ->label('API')
                ->icon('heroicon-m-cloud')
                ->url(fn () => url(route('l5-swagger.default.api')), shouldOpenInNewTab: true)
                ->visible(fn (): bool => 
                $this->canAccessAnyPermission(['permission.mentor','permission.bcf']))
                ,
        ];


        return $menuItems;
    }

    // ... reste des méthodes helper inchangées ...
    // Fonction pour calculer pourcentage parcours a cote de Mon LivretFCM
    private function getMarinProgressBadge(): ?string
{
    $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();

    if (!$marin || !$marin->complements_fcm) {
        return null;
    }

    $parcours = $marin->complements_fcm->donnees_du_parcours->parcoursmarin ?? null;

    if (!is_array($parcours) || empty($parcours['affectations'])) {
        return null;
    }

    

    $progress = \Modules\FcmCommun\Services\CalculHeuresParcoursService::calculerPourcentageEtatValid($parcours);

    return $progress . '%';
}



    private function getMentorMarinsCount(): ?string
    {
        try {
            // if (!$this->canAccessMentorFeatures()) {
            //     return null;
            // }

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
            // if (!$this->canAccessMentorFeatures()) {
            //     return 0;
            // }

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
    
    


    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
