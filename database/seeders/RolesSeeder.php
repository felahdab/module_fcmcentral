<?php

namespace Modules\FcmCentral\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

use App\Models\Role;
use App\Models\Permission;


class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

    //////////////////////
    // ROLES PAR DEFAUT //
    //////////////////////

    // Marin FCM
    // Attribuer
    $role = Role::firstOrCreate([
        "name" => "fcmcentral::marin",
        "guard_name" => "web"
    ]);

    // Permissions fixes
    $permissions = [
        "fcmcentral::livret.voir",
        "fcmcentral::livret.telecharger",
        "fcmcentral::livret.proposer",
    ];

    // Lier
    $this->attachPermissionsToRole($role, $permissions);

    // FIN




    // Mentor  - Chef de Service
    // Attribuer
    $role = Role::firstOrCreate([
        "name" => "fcmcentral::mentor",
        "guard_name" => "web"
    ]);

    // Permissions fixes
    $permissions = [
        "fcmcentral::livret.voir",
        "fcmcentral::livret.telecharger",
        "fcmcentral::livret.valider",
        "fcmcentral::livret.commentaire",
        "fcmcentral::note.creer",
    ];

    // Permissions dynamiques
    $permissions = array_merge(
        $permissions,
        $this->addPermissionsForResource('marin', ['index', 'create', 'edit'])
    );

    $this->attachPermissionsToRole($role, $permissions);

    // FIN

    // FPS
    // Attribuer
    $role = Role::firstOrCreate([
        "name" => "fcmcentral::fps",
        "guard_name" => "web"
    ]);

    // Permissions fixes
    $permissions = [
        "fcmcentral::recherche_annuaire",
        "users.create",
        "fcmcentral::livret.voir",
        "fcmcentral::livret.telecharger",
        "fcmcentral::livret.valider",
        "fcmcentral::livret.annuler",
        "fcmcentral::attribuer.parcours",
        "fcmcentral::attribuer.cohorte",
        "fcmcentral::attribuer.mentor",
        "fcmcentral::marin.suivreEnFcm",
        "fcmcentral::parcours.figer",
        "fcmcentral::note.creer",
        "fcmcentral::note.valider",
    ];

    // Permissions dynamiques
    $permissions = array_merge(
        $permissions,
        $this->addPermissionsForResource('affectation', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competence', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('domaine', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaire', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        //addPermissionsForResource('etat', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('cohorte', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competenceSavoirfaire', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('note', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('objectif', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcours', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursSerialise', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursType', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaireType', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeEtat', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeNote', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('marin', ['index', 'create', 'store', 'edit', 'delete', 'reorder','import','export'])
        // Ajoute d'autres ressources si besoin
    );

    // Lier
    $this->attachPermissionsToRole($role, $permissions);

    // FIN

    // BCF
    // Attribuer
    $role = Role::firstOrCreate([
        "name" => "fcmcentral::bcf",
        "guard_name" => "web"
    ]);

    // Permissions fixes
    $permissions = [
        "fcmcentral::recherche_annuaire",
        "users.create",
        "fcmcentral::livret.voir",
        "fcmcentral::livret.telecharger",
        "fcmcentral::livret.valider",
        "fcmcentral::livret.annuler",
        "fcmcentral::attribuer.parcours",
        "fcmcentral::attribuer.cohorte",
        "fcmcentral::attribuer.mentor",
        "fcmcentral::marin.suivreEnFcm",
        "fcmcentral::parcours.figer",
        "fcmcentral::note.creer",
        "fcmcentral::note.valider",
    ];

    // Permissions dynamiques
    $permissions = array_merge(
        $permissions,
        $this->addPermissionsForResource('affectation', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competence', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('domaine', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaire', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('etat', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('cohorte', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competenceSavoirfaire', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('note', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('objectif', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcours', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursSerialise', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursType', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaireType', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeEtat', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeNote', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('marin', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export'])
        // Ajoute d'autres ressources si besoin
    );

    // Lier
    $this->attachPermissionsToRole($role, $permissions);


    // ADMIN
    // Attribuer
    $role = Role::firstOrCreate([
        "name" => "Administrateur FCM Central",
        "guard_name" => "web"
    ]);

    // Permissions fixes
    $permissions = [
        "fcmcentral::change_configuration_module",
        "fcmcentral::recherche_annuaire",
        "fcmcentral::livret.voir",
        "fcmcentral::livret.telecharger",
        "fcmcentral::livret.valider",
        "fcmcentral::livret.annuler",
        "fcmcentral::attribuer.parcours",
        "fcmcentral::attribuer.cohorte",
        "fcmcentral::attribuer.mentor",
        "fcmcentral::marin.suivreEnFcm",
        "fcmcentral::parcours.figer",
        "fcmcentral::admin",
    ];

    // Permissions dynamiques
    $permissions = array_merge(
        $permissions,
        $this->addPermissionsForResource('affectation', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competence', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('domaine', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaire', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('etat', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('cohorte', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('competenceSavoirfaire', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('note', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('objectif', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcours', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursSerialise', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('parcoursType', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('savoirfaireType', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeEtat', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('typeNote', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export']),
        $this->addPermissionsForResource('marin', ['index', 'create','store', 'edit', 'delete', 'reorder','import','export'])
        // Ajoute d'autres ressources si besoin
    );

    // Lier
    $this->attachPermissionsToRole($role, $permissions);

    // FIN
    


}

/**
     * Crée les permissions pour une ressource et une liste d'actions
     */
    private function addPermissionsForResource(string $resource, array $actions)
    {
        $permissionNames = [];
        foreach ($actions as $action) {
            $permName = "fcmcentral::{$resource}.{$action}";
            $permissionNames[] = $permName;
        }
        return $permissionNames;
    }


    /**
     * Attache une liste de permissions à un rôle
     */
    private function attachPermissionsToRole(Role $role, array $permissions): void
    {
        foreach ($permissions as $permName) {
            $permission = Permission::where("name", $permName)->first();
            if ($permission) {
                // évite les doublons
                if (! $role->permissions()->where('id', $permission->id)->exists()) {
                    $role->permissions()->attach($permission->id);
                }
            } else {
                Log::warning("Permission manquante : $permName");
            }
        }
    }
}