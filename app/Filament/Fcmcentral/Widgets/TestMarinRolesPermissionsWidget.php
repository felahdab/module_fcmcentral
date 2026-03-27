<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TestMarinRolesPermissionsWidget extends BaseWidget
{
    // TODO: remonter ce widget dans Skeletor. Utile d'une façon générale et pas seulement pour la FCM.
    protected static ?int $sort = 5;

    /**
     * Widget visible seulement pour les admins FCM.
     */
    public static function canView(): bool
    {
        // TODO: regarder s'il faut restreindre l'accès à ce widget.
        return false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [
                Stat::make('Non connecté', 0)
                    ->description('Aucun utilisateur connecté')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        }

        // Récupérer les noms de rôles
        $roleNames = $user->getRoleNames(); // Collection
        $rolesCount = $roleNames->count();
        $rolesString = $roleNames->isEmpty() ? 'Aucun rôle' : $roleNames->join(', ');

        // Récupérer les noms de permissions
        $permissionNames = $user->getAllPermissions()->pluck('name'); // Collection des noms
        $permissionsCount = $permissionNames->count();
        $permissionsString = $permissionNames->isEmpty() ? 'Aucune permission' : $permissionNames->join(', ');

        // Limiter l'affichage si trop long
        // if (strlen($rolesString) > 50) {
        //     $rolesString = substr($rolesString, 0, 47) . '...';
        // }

        // if (strlen($permissionsString) > 80) {
        //     $permissionsString = substr($permissionsString, 0, 77) . '...';
        // }

        return [
            // RÔLES
            Stat::make('Mes Rôles', $rolesCount)
                ->description($rolesString)
                ->descriptionIcon('heroicon-m-user-group')
                ->color($rolesCount > 0 ? 'success' : 'warning')
            // ->url('/admin/roles-permissions')
            ,

            // PERMISSIONS
            Stat::make('Mes Permissions', $permissionsCount)
                ->description($permissionsString)
                ->descriptionIcon('heroicon-m-key')
                ->color($permissionsCount > 0 ? 'info' : 'warning')
            // ->url('/admin/roles-permissions')
            ,

            // TEST FCM
            // Stat::make('FCM Autorisé', $user->can('fcmcentral::suivre_en_fcm') ? 'OUI' : 'NON')
            //     ->description('fcmcentral::suivre_en_fcm')
            //     ->descriptionIcon('heroicon-m-check-circle')
            //     ->color($user->can('fcmcentral::suivre_en_fcm') ? 'success' : 'danger')
            //     ->url('/admin/roles-permissions'),

            // STATUT ADMIN FCM
            // Stat::make('Admin FCM', $user->can('fcmcentral::admin') ? 'OUI' : 'NON')
            //     ->description('fcmcentral::admin')
            //     ->descriptionIcon('heroicon-m-shield-check')
            //     ->color($user->can('fcmcentral::admin') ? 'success' : 'danger')
            //     ->url('/admin/roles-permissions'),
        ];
    }
}
