# Suivi des Mentors — Module FcmCentral

> **But de ce document** : expliquer la page « Suivi des Mentors » (vue d'ensemble + détection des décalages d'unité entre mentor et mentoré) et le filtre préventif à l'attribution, leur fonctionnement, et comment les faire évoluer.

---

## Contexte / besoin métier

Un marin mentoré peut être suivi par un ou plusieurs mentors, et un mentor peut suivre plusieurs marins. Ces relations sont indépendantes de l'unité d'affectation. Quand un marin **change d'unité** (mutation), rien n'empêchait jusqu'ici le lien mentor/mentoré de rester en place alors que les deux personnes ne sont plus dans la même unité — situation qui n'est en général plus pertinente et passait inaperçue faute de vue d'ensemble.

Deux briques répondent à ce besoin, toutes deux dans la page **Suivi des Mentors** :

1. **Filtre préventif à l'attribution** — quand on choisit un mentor pour un marin, seuls les mentors de la même unité sont proposés par défaut.
2. **Vue d'ensemble + alerte visuelle** — pour repérer les décalages déjà existants (marin muté après coup, mentor attribué avant un déménagement, etc.), qui ne peuvent pas être empêchés à la source.

> Un `MarinUniteObserver` avait été envisagé pour notifier automatiquement au moment du changement d'unité, mais a été retiré : il faisait doublon avec la vue d'ensemble ci-dessous et ajoutait de la complexité (observer sur 3 classes Eloquent différentes, garde-fous fragiles) pour un gain limité. Le choix retenu est le filtre préventif décrit plus bas, complété par une revue manuelle via la page.

---

## 1. Modèle `MentorAssignment`

Fichier : `Modules/FcmCentral/app/Models/MentorAssignment.php`

La relation mentor/mentoré est stockée dans la table pivot `fcmcentral_fcm_marin_mentors` :

| Colonne | Référence |
|---|---|
| `fcm_marin_id` | `fcmcentral_fcm_marins.id` (le **mentoré**) |
| `marin_id` | `rh_marins.id` (le **mentor**) |

`FcmMarin::mentors()` (belongsToMany) permet de lister les mentors d'un marin, mais ne donne pas directement l'inverse — « pour un mentor donné, tous ses mentorés » — sous une forme exploitable en table Filament paginée/recherchable. `MentorAssignment` expose donc **une ligne Eloquent par couple (mentor, mentoré)**, avec deux relations `belongsTo` :

```php
$assignment->mentor;              // Marin (FcmCentral) — le mentor
$assignment->fcmMarin;            // FcmMarin — la fiche FCM du mentoré
$assignment->fcmMarin->marin;     // Marin (FcmCentral) — le mentoré lui-même
```

C'est ce modèle qui sert de base à la page de suivi.

---

## 2. Page « Suivi des Mentors »

Fichier : `Modules/FcmCentral/app/Filament/Fcmcentral/Pages/Suivi/SuiviMentorsPage.php`
Vue : `Modules/FcmCentral/resources/views/filament/fcmcentral/pages/suivi/suivi-mentors.blade.php`
Permission requise : `fcmcentral::suivi.mentor` (ajoutée dans `PermissionsSeeder`)
Menu : groupe **Suivi**, comme les autres pages `Suivi*Page`.

### Fonctionnement

- **Requête de base** (`buildQuery()`) : `MentorAssignment::query()` avec eager-loading de `mentor` et `fcmMarin.marin` (grade + unité).
- **Regroupement** : la table est groupée par mentor (`->groups()` + `->defaultGroup('marin_id')`), chaque section repliable affiche un mentor et la liste de ses mentorés en dessous.
- **Recherche** : recherche libre sur le nom/prénom du mentor et du mentoré, via `->searchable(query: ...)` avec `whereHas` explicite (⚠️ voir piège ci-dessous).
- **Colonne Alerte** : icône ⚠️ (rouge) / ✅ (vert) calculée par `hasUniteMismatch()`, qui compare `mentor->unite_id` et `fcmMarin->marin->unite_id`.
- **Filtre** « Uniquement les décalages d'unité » : filtre à bascule (`Filter::make('decalage_unite')`) qui compare les unités via une sous-requête SQL directement sur la table pivot, pour rester compatible avec la pagination.
- **Action « Gérer les mentors »** : ouvre une modale de sélection multiple des mentors pour le mentoré de la ligne, et déclenche l'événement `UserGeneratedEvent` de type `ATTRIBUER_MENTOR_MARIN` (même mécanisme que `AffecterMentorAction` sur la fiche marin), traité par `MentorMarinListener` qui fait `sync()` sur la relation `mentors()`.

### Filtre préventif : proposer d'abord les mentors de la même unité

Dans l'action « Gérer les mentors », le `Select::make('mentors')` filtre ses options par défaut sur l'unité du mentoré :

```php
->options(function (MentorAssignment $record) {
    $uniteId = $record->fcmMarin?->marin?->unite_id;
    $mentorsActuels = $record->fcmMarin?->mentors->pluck('id')->all() ?? [];

    return Marin::query()
        ->withoutGlobalScope(MarinFiltreParUniteScope::class)
        ->whereNull('deleted_at')
        ->where(fn ($q) => $q->when($uniteId, fn ($q) => $q->where('unite_id', $uniteId))
            ->when($mentorsActuels, fn ($q) => $q->orWhereIn('id', $mentorsActuels)))
        ->orderBy('nom')
        ->get()
        ->mapWithKeys(...);
})
```

Point important : les mentors **déjà attribués** restent toujours dans la liste, même s'ils sont dans une autre unité (`orWhereIn('id', $mentorsActuels)`). Sans ce filet de sécurité, ouvrir la modale sur un couple déjà en décalage aurait fait disparaître silencieusement l'ancien mentor de la liste des options — et enregistrer sans y prêter attention l'aurait retiré (`sync()` remplace toute la liste). Le filtre ne fait donc que **restreindre les nouveaux choix**, jamais **masquer un état existant**.

Ce filtre ne couvre que le moment de l'attribution. Si un marin change d'unité *après* qu'un mentor cohérent lui a été attribué, rien ne l'empêche — d'où le second mécanisme :

### ⚠️ Piège : `->searchable()` sur une colonne relationnelle

Ne **jamais** faire `->searchable(['relation.colonne'])` sur une `TextColumn::make('relation.colonne')` : Filament traite chaque entrée du tableau comme une **colonne JSON brute** de la table courante, pas comme un chemin de relation — ce qui produit des requêtes du type :

```sql
json_unquote(json_extract(`mentor`, '$."nom"'))
```

et lève `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'mentor'`.

**Solution** : utiliser `->searchable(query: fn (Builder $query, string $search) => $query->orWhereHas('relation', fn ($q) => $q->where(...)))` pour construire la clause de recherche explicitement.

### Scope par unité (`MarinFiltreParUniteScope`)

`Modules/RH/Models/Marin` applique un scope global qui restreint les requêtes à l'unité de l'utilisateur connecté (sauf permission `rh::voir_tous_les_marins`, voir `DOC_PERMISSIONS.md`). Cette page a **besoin de voir toutes les unités** pour détecter les décalages, donc chaque requête vers `Marin` (eager-loading, recherche, options de la modale) désactive explicitement ce scope avec `->withoutGlobalScope(MarinFiltreParUniteScope::class)`.

---

## Pour aller plus loin

- Ajouter un job planifié (cron) qui recense périodiquement les décalages et envoie un résumé, si la revue manuelle de la page s'avère insuffisante en pratique.
- Voir `DOC_PERMISSIONS.md` pour le détail de `MarinFiltreParUniteScope` et des permissions `voir_tous_les_marins`.
