<?php

namespace Modules\FcmCentral\Helpers;


    use Filament\Navigation\NavigationBuilder;
    use Filament\Navigation\NavigationGroup;
    use Filament\Navigation\NavigationItem;
    use Modules\FcmCentral\Filament\Fcmcentral\Pages\Dashboard;
    use Modules\FcmCentral\Filament\Fcmcentral\Pages\MonLivretDeFcm;
    use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuairePage;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\CohorteResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursSerialiseResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\FonctionResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\CompetenceResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\SavoirFaireResource;
    use Modules\FcmCentral\Filament\Fcmcentral\Resources\ActiviteResource;
    
    class NavigationHelper
    {
        /**
         * Construction de la navigation complète
         */
        public static function buildNavigation(NavigationBuilder $builder): NavigationBuilder
        {
            return $builder->groups([
                self::buildMainGroup(),
                self::buildAdministrationGroup(),
                self::buildParcoursGroup(),
                self::buildOutilsGroup(),
                self::buildSystemGroup(),
            ]);
        }
    
        /**
         * Groupe principal (Dashboard, Mon livret, Mes marins)
         */
        private static function buildMainGroup(): NavigationGroup
        {
            return NavigationGroup::make()
                ->items([
                    // Dashboard
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
                ->badge(fn () => self::getMarinProgressBadge())
                ->visible(fn (): bool => self::canAccessMarinLivret()),
            
            // Mes marins FCM - visible seulement pour les mentors
            NavigationItem::make('Mes marins FCM')
                ->icon('heroicon-o-users')
                ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                ->url(fn (): string => MarinResource::getUrl('index'))
                ->badge(fn () => self::getMentorMarinsCount())
                ->visible(fn (): bool => self::canAccessMentorFeatures()),
                ]);
        }
    
        /**
         * Groupe Administration
         */
        private static function buildAdministrationGroup(): NavigationGroup
        {
            return NavigationGroup::make('Administration')
                ->items([
                    NavigationItem::make('Gestion des marins')
                        ->icon('heroicon-o-users')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'marin'))
                        ->url(fn (): string => MarinResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('gerer_mentors')),
                    
                    NavigationItem::make('Gestion des cohortes')
                        ->icon('heroicon-o-user-group')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'cohorte'))
                        ->url(fn (): string => CohorteResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('gerer_cohortes')),
                    
                    NavigationItem::make('Attribution cohortes')
                        ->icon('heroicon-o-plus-circle')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.attribution-cohortes'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('attribuer_cohorte')),
                    
                    NavigationItem::make('Attribution mentors')
                        ->icon('heroicon-o-user-plus')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.attribution-mentors'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('attribuer_mentor')),
                    
                    NavigationItem::make('Statistiques')
                        ->icon('heroicon-o-chart-bar')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.statistiques'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('voir_statistiques')),
                ])
                //->visible(fn (): bool => self::canAccess('bcf'))
                ;
        }
    
        /**
         * Groupe Gestion des parcours
         */
        private static function buildParcoursGroup(): NavigationGroup
        {
            return NavigationGroup::make('Gestion des parcours')
                ->items([
                    NavigationItem::make('Parcours figés')
                        ->icon('heroicon-o-lock-closed')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours-serialise'))
                        ->url(fn (): string => ParcoursSerialiseResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('bcf')),
                    
                    NavigationItem::make('Parcours')
                        ->icon('heroicon-o-map')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'parcours') && !str_contains(request()->route()?->getName() ?? '', 'serialise'))
                        ->url(fn (): string => ParcoursResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('attribuer_parcours')),
                    
                    NavigationItem::make('Fonctions')
                        ->icon('heroicon-o-briefcase')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'fonction'))
                        ->url(fn (): string => FonctionResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('bcf')),
                    
                    NavigationItem::make('Compétences')
                        ->icon('heroicon-o-star')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'competence'))
                        ->url(fn (): string => CompetenceResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('bcf')),
                    
                    NavigationItem::make('Savoir-faire')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'savoir-faire'))
                        ->url(fn (): string => SavoirFaireResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('bcf')),
                    
                    NavigationItem::make('Stages et tâches')
                        ->icon('heroicon-o-rectangle-stack')
                        ->isActiveWhen(fn (): bool => str_contains(request()->route()?->getName() ?? '', 'activite'))
                        ->url(fn (): string => ActiviteResource::getUrl('index'))
                        ->visible(fn (): bool => self::canAccess('bcf')),
                    
                    NavigationItem::make('Validation parcours')
                        ->icon('heroicon-o-check-circle')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.validation-parcours'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('valider_element_parcours')),
                ])
                //->visible(fn (): bool => self::canAccess('bcf'))
                ;
        }
    
        /**
         * Groupe Outils
         */
        private static function buildOutilsGroup(): NavigationGroup
        {
            return NavigationGroup::make('Outils')
                ->items([
                    NavigationItem::make('Recherche annuaire')
                        ->icon('heroicon-o-magnifying-glass')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.recherche-annuaire-page'))
                        ->url(fn (): string => RechercheAnnuairePage::getUrl())
                        ->visible(fn (): bool => self::canAccess('recherche_annuaire')),
                    
                    NavigationItem::make('Livrets')
                        ->icon('heroicon-o-document-duplicate')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.livrets'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('voir_les_livrets')),
                    
                    NavigationItem::make('Téléchargements')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.telechargements'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('telecharger_les_livrets')),
                    
                    NavigationItem::make('Fiches bilan')
                        ->icon('heroicon-o-document-chart-bar')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.fcm-central.pages.fiches-bilan'))
                        ->url(fn (): string => '#') // TODO: Remplacer par votre URL
                        ->visible(fn (): bool => self::canAccess('voir_fiche_bilan')),
                ])
                // ->visible(fn (): bool => 
                //     self::canAccess('recherche_annuaire') || 
                //     self::canAccess('voir_les_livrets') || 
                //     self::canAccess('telecharger_les_livrets') || 
                //     self::canAccess('voir_fiche_bilan')
                // )
                ;
        }
    
        /**
         * Groupe Administration système
         */
        private static function buildSystemGroup(): NavigationGroup
        {
            return NavigationGroup::make('Administration système')
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
                //->visible(fn (): bool => self::canAccess('admin'))
                ;
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



        public static function  getMarinProgressBadge(): ?string
    {
        try {
            if (!self::canAccessMarinLivret()) {
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

    public static function getMentorMarinsCount(): ?string
    {
        try {
            if (!self::canAccessMentorFeatures()) {
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

    public static function getMentorPendingValidationsCount(): int
    {
        try {
            if (!self::canAccessMentorFeatures()) {
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
    
      
    }
    
    
  