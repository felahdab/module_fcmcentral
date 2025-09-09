<?php

namespace Modules\FcmCentral\Traits;

use Modules\FcmCentral\Models\Marin;

trait FcmAccessControl
{
    /**
     * Vérifie si l'utilisateur est un marin FCM (non mentor) avec parcours
     */
    public static function canAccessMarinLivret(): bool
    {
        $user = auth()->user();

        // Exclure les super_admin
        if ($user && $user->admin == 1) {
            return false;
        }
        
        $marin = Marin::fromCurrentUser();
        
        // Accessible seulement aux marins FCM avec parcours (non mentors)
        return $marin !== null && 
               $marin->suivi_en_fcm &&
               $marin->complements_fcm !== null &&
               $marin->complements_fcm->donnees_du_parcours !== null &&
               $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null &&
               !Marin::estMentor($marin);
    }

    /**
     * Vérifie si l'utilisateur est un mentor
     */
    public static function canAccessMentorFeatures(): bool
    {
        $user = auth()->user();
        
        // Exclure les super_admin
        if ($user && $user->admin == 1) {
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
        
        if (!$user) {
            return false;
        }

        if ($user->admin == 1) {
            return true;
        }

        
        // Vérifier si c'est un super admin
        // if (method_exists($user, 'IsSuperAdmin') && $user->IsSuperAdmin()) {
        //     return true;
        // }

        // Vérifier si user->admin == 1
        // if (property_exists($user, 'admin') && $user->admin == 1) {
        //     return true;
        // }

        
        // Ou vérifier les rôles/permissions spécifiques
        // if (method_exists($user, 'hasRole')) {
        //     return $user->hasRole(['fcmcentral::superviseur','admin','super-admin']);
        // }
        
        return false;
    }

    /**
     * Vérifie si le marin a un parcours FCM complet
     */
    public static function marinHasParcours(?Marin $marin = null): bool
    {
        if (!$marin) {
            $marin = Marin::fromCurrentUser();
        }

        return $marin && 
               $marin->complements_fcm !== null &&
               $marin->complements_fcm->donnees_du_parcours !== null &&
               $marin->complements_fcm->donnees_du_parcours->parcoursmarin !== null;
    }

    /**
     * Vérifie si l'utilisateur peut consulter des livrets d'autres marins
     */
    public static function canConsultOthersLivret(): bool
    {
        return static::canAccessMentorFeatures() || static::canAccessAdminMenus();
    }

    /**
     * Vérifie si l'utilisateur peut valider des éléments
     */
    public static function canValidateElements(): bool
    {
        $user = auth()->user();
        
        return $user && 
               $user->can('fcmcentral::valider_element_parcours') &&
               (static::canAccessMentorFeatures() || static::canAccessAdminMenus());
    }
}
