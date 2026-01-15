<?php

namespace Modules\FcmCentral\Traits;
use Illuminate\Contracts\Auth\Authenticatable;

use Illuminate\Support\Str;

trait FcmPermissionTrait
{
    /**
     * Vérifie si l'utilisateur a la permission demandée (ex: widget.marin, widget.mentor, page.bcf, etc.)
     * @param string $permission
     * @return bool
     */
    public static function canAccessPermission(string $permission): bool
    {
        $user = auth()->user();

        


        if (!$user) {
            return false;
        }

        // Si la permission commence par "widget." ou "page.", on la transforme en "fcmcentral::widget.marin" par exemple
        if (!Str::startsWith($permission, 'fcmcentral::')) {
            $permission = 'fcmcentral::' . $permission;
        }

        return $user->can($permission);
    }

    /**
     * Vérifie si l'utilisateur a au moins une permission dans la liste
     * @param array $permissions
     * @return bool
     */
    public static function canAccessAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (static::canAccessPermission($permission)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Vérifie si l'utilisateur est administrateur
     */
    private static function isAdmin(?Authenticatable $user): bool
    {
        return $user && $user->can('fcmcentral::admin');
    }



    /**
     * Vérifie si l'utilisateur est administrateur
     */
    private function canAccesMarinLivret(): bool
    {
        $marin = \Modules\FcmCentral\Models\Marin::fromCurrentUser();
        return $marin
        && $marin->complements_fcm
        && $marin->complements_fcm->donnees_du_parcours;
    }



     /**
     * Acceder a des modules specifiques hors ressources
     *  mettre en premier le module souhaite, en 2 un array si d'autre ont acces, en 3 si admin a acces
     *  Exemple : canAccessModuleWithPermissions('widget.marin',['widget.mentor','widget.bcf'], true)
     *  si aucun argument tout le monde peut le voir
     */
    public static function canAccessModuleWithPermissions(string $module = '', array $extraPermissions = [], bool $allowAdmin = false): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
    
        // Si aucun module ni permission supplémentaire, tout le monde a le droit
        if ($module === '' && empty($extraPermissions)) {
            return true;
        }
    
        // Bloquer l'accès aux admins sauf si explicitement autorisé
        if (!$allowAdmin && $module !== 'admin' && self::isAdmin($user)) {
            return false;
        }
    
        // Permission principale du module (ex: widget.marin, page.configuration, etc.)
        if ($module !== '') {
            $modulePermission = str_starts_with($module, 'fcmcentral::')
                ? $module
                : 'fcmcentral::' . $module;
    
            if ($user->can($modulePermission)) {
                return true;
            }
        }
    
        // Vérifie les permissions supplémentaires
        foreach ($extraPermissions as $perm) {
            if (!str_starts_with($perm, 'fcmcentral::')) {
                $perm = 'fcmcentral::' . $perm;
            }
            if ($user->can($perm)) {
                return true;
            }
        }
    
        return false;
    }
    
    





}
