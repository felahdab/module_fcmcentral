# Suivi des Mentors & Observer d'unité — Module FcmCentral

> **But de ce document** : expliquer la page « Suivi des Mentors » et l'observer qui détecte les décalages d'unité entre mentor et mentoré, leur fonctionnement, et comment les faire évoluer.

---

## Contexte / besoin métier

Un marin mentoré peut être suivi par un ou plusieurs mentors, et un mentor peut suivre plusieurs marins. Ces relations sont indépendantes de l'unité d'affectation. Quand un marin **change d'unité** (mutation), rien n'empêchait jusqu'ici le lien mentor/mentoré de rester en place alors que les deux personnes ne sont plus dans la même unité — situation qui n'est en général plus pertinente et passait inaperçue faute de vue d'ensemble.

Deux briques répondent à ce besoin :

1. **La page « Suivi des Mentors »** — vue d'ensemble manuelle, consultée à la demande.
2. **`MarinUniteObserver`** — détection automatique au moment du changement d'unité, avec notification.

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

## 3. `MarinUniteObserver` — détection automatique

Fichier : `Modules/FcmCentral/app/Observers/MarinUniteObserver.php`
Enregistrement : `Modules/FcmCentral/app/Providers/EventServiceProvider.php` → méthode `boot()` → `::observe(MarinUniteObserver::class)` appelé sur **les trois classes** `RH\Models\Marin`, `FcmCommun\Models\Marin` et `FcmCentral\Models\Marin` (voir piège n°1 plus bas).

### Pourquoi l'observer est enregistré côté FcmCentral et pas côté RH

Le module `RH` est une dépendance de base : `FcmCentral` en dépend, jamais l'inverse. Un observer qui a besoin de connaître les notions de mentor/mentoré (propres à FcmCentral) ne doit donc pas être déclaré sur le modèle RH lui-même (ni via l'attribut `#[ObservedBy]` sur `Modules/RH/Models/Marin`, qui importerait une classe FcmCentral dans RH). Il est enregistré **depuis FcmCentral**, qui a le droit de connaître RH, via `Marin::observe()` dans son propre `EventServiceProvider::boot()`.

### Déclenchement

```php
public function updated(Marin $marin): void
{
    if (!$marin->isDirty('unite_id')) {
        return;
    }
    // ...
}
```

- Ne se déclenche que sur un `save()` qui **modifie effectivement** `unite_id` (pas à chaque sauvegarde).
- Caste le `Marin` RH en `Modules\FcmCentral\Models\Marin` via le helper global `cast_as_eloquent_descendant()` pour accéder aux méthodes FCM (`isMentor()`, `marinsSupervises()`, `getMentors()`).
- Vérifie les décalages **dans les deux sens** :
  - si le marin est **mentor**, compare son unité (la nouvelle) à celle de chacun de ses mentorés ;
  - si le marin est **mentoré**, compare son unité à celle de chacun de ses mentors.
- S'il y a au moins un décalage, envoie une **notification Filament en base de données** (`Notification::make()->sendToDatabase(...)`) à tous les utilisateurs ayant la permission `fcmcentral::suivi.mentor` (via le scope Spatie `User::permission(...)`).

### ⚠️ Deux pièges trouvés en écrivant les tests (`MarinUniteObserverTest.php`)

1. **Les événements Eloquent sont scopés à la classe réellement instanciée, pas héritée.** `RH\Models\Marin`, `FcmCommun\Models\Marin` et `FcmCentral\Models\Marin` représentent la même ligne de table par héritage Eloquent, mais un `Marin::observe()` enregistré sur une seule de ces classes ne se déclenche **que** pour les sauvegardes faites via cette classe précise — pas via ses sous-classes ni ses classes parentes. `EventServiceProvider::boot()` observe donc **les trois classes** pour couvrir tous les points d'entrée (`RH\MarinResource`, actions FcmCentral, etc.).

2. **`suivi_en_fcm` ne convient pas comme garde-fou générique.** `FcmCentral\Models\Marin::getSuiviEnFcmAttribute()` renvoie `$this->complementsFcm()->exists()` — donc `true` seulement si le marin a sa **propre** ligne `fcmcentral_fcm_marins`. Un **mentor** n'a pas forcément cette ligne (ce n'est pas lui qui est suivi, c'est son mentoré) : un premier jet de l'observer qui filtrait sur `suivi_en_fcm` avant toute vérification ratait donc systématiquement le cas « le mentor change d'unité ». L'observer ne fait plus cette vérification en amont : `isMentor()`/`getMentors()` renvoient naturellement `false`/vide quand ils ne s'appliquent pas, ce qui suffit à éviter les faux positifs.

### Ce que l'observer ne fait PAS

- Il ne bloque pas le changement d'unité (pas de `updating` qui empêcherait le `save()`).
- Il ne réaffecte rien automatiquement — la résolution se fait manuellement sur la page **Suivi des Mentors**.
- Il n'envoie qu'une notification en base (cloche Filament) : pas d'email, pas de SMS.

---

## Pour aller plus loin

- Étendre l'action « Gérer les mentors » pour proposer aussi les mentors d'une autre unité de façon plus visible (actuellement la liste des mentors possibles contourne déjà le scope par unité, donc c'est déjà possible, mais l'UX ne le met pas particulièrement en avant).
- Ajouter un lien direct depuis la notification vers la page Suivi des Mentors, filtrée sur le couple concerné.
- Voir `DOC_PERMISSIONS.md` pour le détail de `MarinFiltreParUniteScope` et des permissions `voir_tous_les_marins`.

