<?php
namespace Modules\FcmCentral\Traits;

trait FilamentPermissionTrait
{
    //protected static array $permissions = [];

    protected static function hasAnyPermission(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        foreach (static::$permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }
        return false;
    }

    // Acces a la page
    
    public static function canAccess(array $parameters = []): bool
    {
        return static::hasAnyPermission();
    }

    // Permissions

    public static function canViewAny(): bool
    {
        return static::hasAnyPermission();
    }

    public static function canCreate(): bool
    {
        return static::hasAnyPermission();
    }

    public static function canEdit($record): bool
    {
        return static::hasAnyPermission();
    }

    public static function canDelete($record): bool
    {
        return static::hasAnyPermission();
    }

    public static function canDeleteAny(): bool
    {
        return static::hasAnyPermission();
    }

    public static function canOrder(): bool
    {
        return static::hasAnyPermission();
    }

    // Méthode de redirection à utiliser dans mount()
    public function redirectIfNoAccess(string $route = 'filament.FCM Central.pages.dashboard')
    {
        if (!static::canAccess()) {
            return redirect()->route($route);
        }
    }
}
