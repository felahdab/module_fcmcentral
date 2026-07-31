# Commandes Artisan `skeletor:*` — Documentation technique

> **But de ce document** : référence de toutes les commandes Artisan personnalisées du projet (`app/Console/Commands/`), et en particulier **comment générer/mettre à jour les permissions** — question qui revient souvent.

---

## Je veux juste générer les nouvelles permissions, comment je fais ?

Ça dépend de **où** vivent les permissions que vous venez d'ajouter.

### Cas 1 — Vous avez ajouté des permissions dans le `PermissionsSeeder` d'un module

C'est le cas le plus courant (ex: `fcmcentral::piece_jointe.voir` ajouté dans `Modules/FcmCentral/database/seeders/PermissionsSeeder.php`).

```bash
# Un module précis
php artisan skeletor:seed-permissions FcmCentral

# Tous les modules activés d'un coup (ceux qui ont un PermissionsSeeder)
php artisan skeletor:seed-permissions
```

Chaque module a son propre `PermissionsSeeder`, **aucun d'eux n'est appelé automatiquement** par le seeder racine (`database/seeders/DatabaseSeeder.php`) — il faut les lancer après avoir ajouté vos permissions dedans. Voir section dédiée plus bas.

> Le seeder utilise `Permission::firstOrCreate(...)`, donc le relancer plusieurs fois est sans danger (pas de doublon).

### Cas 2 — Vous avez ajouté une nouvelle `Resource` Filament avec une `Policy`

Les permissions CRUD (`<slug>.index`, `.create`, `.update`, `.delete`, etc.) ne sont **pas** dans le seeder — elles sont générées automatiquement à partir des `Policy` de vos modèles :

```bash
php artisan skeletor:generate-permissions-for-filament-resources-command
```

Voir section dédiée plus bas — **votre `Policy` doit étendre `GenericSkeletorPolicy`**, sinon cette commande plante (voir "Piège connu" en bas de ce document).

### Cas 3 — Permissions liées aux routes

```bash
php artisan permission:create-permission-routes
```

Voir section dédiée plus bas. Cette commande est aussi appelée automatiquement par `database/seeders/PermissionsSeeder.php` (racine), donc un `php artisan db:seed` normal la déclenche déjà.

### Une fois les permissions créées

Elles existent en base mais ne sont attribuées à **personne**. Il faut ensuite les donner à un rôle (via l'interface Filament, ou par code : `$role->givePermissionTo('fcmcentral::piece_jointe.voir')`), puis le rôle à un utilisateur — voir `Modules/FcmCentral/DOC_PERMISSIONS.md` pour les rôles suggérés (côté FcmCentral).

---

## `skeletor:seed-permissions {module?}`

**Fichier** : `app/Console/Commands/SkeletorSeedPermissionsCommand.php`

Wrapper autour de `php artisan db:seed --class="Modules\{Module}\Database\Seeders\PermissionsSeeder"`, pour ne pas avoir à retaper le nom de classe complet à chaque fois.

```bash
# Un module précis
php artisan skeletor:seed-permissions FcmCentral

# Sans argument : tous les modules activés (Module::allEnabled())
php artisan skeletor:seed-permissions
```

Sans argument, elle détecte les modules activés (`modules_statuses.json`), lance le `PermissionsSeeder` de ceux qui en ont un, et affiche en fin d'exécution un récapitulatif :

```
Modules seedés : FcmCentral, Planificationgpd, Resa
Modules ignorés (pas de PermissionsSeeder) : FcmCommun, RH
```

Un module sans `PermissionsSeeder` (ex: `FcmCommun`, `RH`) est simplement ignoré, pas d'erreur.

---

## `skeletor:generate-permissions-for-filament-resources-command`

**Fichier** : `app/Console/Commands/SkeletorGeneratePermissionsForFilamentResourcesCommand.php`

**Ce qu'elle fait** : parcourt **toutes les `Resource` Filament de tous les panels enregistrés** (`Filament::getPanels()`), récupère la `Policy` associée à chaque modèle (`Gate::getPolicyFor($model)`), et crée en base les 10 permissions CRUD standard pour chacune :

```
<slug>.index, .create, .store, .update, .delete,
.deleteAny, .restore, .forceDelete, .forceDeleteAny, .reorder
```

Le `<slug>` vient de `$policy->getSlug()` — la propriété `protected $slug` définie sur chaque `Policy`.

```bash
php artisan skeletor:generate-permissions-for-filament-resources-command
```

**Quand la relancer** : à chaque fois qu'une nouvelle `Resource` Filament (avec sa `Policy`) est ajoutée à un module, pour générer ses permissions CRUD sans avoir à les écrire à la main dans un seeder.

**Idempotent** : `Permission::firstOrCreate(...)`, sans risque à relancer plusieurs fois.

---

## `permission:create-permission-routes`

**Fichier** : `app/Console/Commands/CreateRoutePermissionsCommand.php`

Génère des permissions à partir des routes déclarées (mécanisme séparé des permissions liées aux `Resource`/`Policy` ci-dessus — utile pour protéger des routes qui n'ont pas de modèle/Policy Filament derrière). Déjà appelée automatiquement par `database/seeders/PermissionsSeeder.php` (racine) à chaque `php artisan db:seed`.

```bash
php artisan permission:create-permission-routes
```

---

## Autres commandes `skeletor:*`

| Commande | Description |
|---|---|
| `skeletor:generate-api-docs` | Génère `storage/api-docs/api-docs.json` pour SwaggerUI. |
| `skeletor:give-uuid-to-users-and-systems` | S'assure que tous les `User` et `Remotesystem` ont un UUID renseigné (idempotent, à lancer après import de données sans UUID). |
| `skeletor:migrate-database {table}` | Migre les données d'une table depuis la base "source" vers la base "destination" (paramètre : nom de la table). |
| `skeletor:publish-module-doc {module} {--force}` | Publie les fichiers de documentation d'un module dans le dossier central `resources/doc` (pour LaRecipe). `--force` pour écraser. |
| `skeletor:test-remote-system {url} {token}` | Teste la connectivité vers un système distant. |
| `skeletor:fix-users-from-ldap` | Corrige/ajuste les comptes utilisateurs à partir des données LDAP. |
| `skeletor:display-most-used-routes-command {--user-email=*}` | Affiche un tableau des routes les plus utilisées (optionnellement filtré par email utilisateur). |
| `skeletor:test-ldap-connexion` | Teste la connexion au serveur LDAP configuré. |
| `skeletor:create-tenant {tenantid}` | En mode multi-tenant, crée un nouveau tenant à partir de son slug. |
| `skeletor:adjust-google-fonts-urls-command` | Met à jour les URLs Google Fonts dans `fonts.css` selon l'instance/préfixe courant. |
| `skeletor:send-test-email-command` | Envoie un email de test pour vérifier la configuration SMTP. |

D'autres commandes existent sans le préfixe `skeletor:` (`ChangeDbPasswordCommand`) — voir `app/Console/Commands/` directement pour la liste exhaustive.

---
