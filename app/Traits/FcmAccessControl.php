<?php

namespace Modules\FcmCentral\Traits;

use Modules\FcmCentral\Models\Marin;
use Illuminate\Contracts\Auth\Authenticatable;

trait FcmAccessControl
{
    /**
     * Vérifie si l'utilisateur est un marin FCM (non mentor) avec parcours
     */
    public static function canAccessMarinLivret(): bool
    {
        $user = auth()->user();

        if (self::isAdmin($user)) {
            return false;
        }

        $marin = Marin::fromCurrentUser();

        return $marin !== null
            && $marin->suivi_en_fcm
            && $marin->complements_fcm !== null
            && $marin->complements_fcm->donnees_du_parcours !== null
            && $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null
            && !Marin::estMentor($marin);
    }

    /**
     * Vérifie si l'utilisateur est un mentor
     */
    public static function canAccessMentorFeatures(): bool
    {
        $user = auth()->user();

        if (self::isAdmin($user)) {
            return false;
        }

        $marin = Marin::fromCurrentUser();
        return $marin !== null && Marin::estMentor($marin);
    }

    /**
     * Vérifie si l'utilisateur peut accéder aux menus administratifs
     */
    public static function canAccessAdminMenus(): bool
    {
        $user = auth()->user();

        return $user && $user->can('fcmcentral::droits_admin');
    }

    /**
     * Vérifie si le marin a un parcours FCM complet
     */
    public static function marinHasParcours(?Marin $marin = null): bool
    {
        if (!$marin) {
            $marin = Marin::fromCurrentUser();
        }

        return $marin
            && $marin->complements_fcm !== null
            && $marin->complements_fcm->donnees_du_parcours !== null
            && $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null;
    }

    /**
     * Vérifie si l'utilisateur peut consulter des livrets d'autres marins
     */
    public static function canConsultOthersLivret(): bool
    {
        return self::canAccessMentorFeatures() || self::canAccessAdminMenus();
    }

    /**
     * Vérifie si l'utilisateur peut valider des éléments
     */
    public static function canValidateElements(): bool
    {
        return self::hasPermissionAndAccess('fcmcentral::valider_element_parcours');
    }

    /**
     * Vérifie si l'utilisateur peut gérer le parcours
     */
    public static function canGestionParcours(): bool
    {
        return self::hasPermissionAndAdminAccess('fcmcentral::gerer_parcours');
    }

    /**
     * Vérifie si l'utilisateur peut gérer les cohortes
     */
    public static function canGestionCohorte(): bool
    {
        return self::hasPermissionAndAdminAccess('fcmcentral::gerer_cohorte');
    }

    /**
     * Vérifie si l'utilisateur peut gérer les marins
     */
    public static function canGestionMarin(): bool
    {
        return self::hasPermissionAndAdminAccess('fcmcentral::gerer_marin');
    }

    /**
     * Vérifie si l'utilisateur peut gérer les marins
     */
    public static function canAttribuerParcours(): bool
    {
        return self::hasPermissionAndAdminAccess('fcmcentral::attribuer_parcours');
    }

    /**
     * Vérifie si l'utilisateur peut gérer les marins
     */
    public static function canGestionNote(): bool
    {
        return self::hasPermissionAndAdminAccess('fcmcentral::gerer_note');
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    private static function isAdmin(?Authenticatable $user): bool
    {
        return $user && $user->can('fcmcentral::droits_admin');
    }

    /**
     * Vérifie si l'utilisateur a une permission et un accès mentor ou admin
     */
    private static function hasPermissionAndAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user
            && $user->can($permission)
            && (self::canAccessMentorFeatures() || self::canAccessAdminMenus());
    }

    /**
     * Vérifie si l'utilisateur a une permission et un accès admin uniquement
     */
    private static function hasPermissionAndAdminAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user
            && $user->can($permission)
            && self::canAccessAdminMenus();
    }
}
