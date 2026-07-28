# Fiche Bilan — Documentation technique

> **But de ce document** : comprendre comment fonctionne cette page pour pouvoir la reprendre sans avoir à tout relire ligne par ligne. Écrit pour quelqu'un qui découvre le code.

---

## À quoi sert cette page

La Fiche Bilan est la page de synthèse d'un marin en FCM : sa progression sur son parcours (stages, EAD, compagnonnage), ses notes, ses jalons/gels, et — plus récemment — les pièces jointes attachées aux savoir-faire validés et des documents annexes libres.

URL Filament : ressource `MarinResource`, page `ViewRecord` accessible depuis la fiche d'un marin.

---

## Architecture générale

Le code est découpé en **deux couches** :

1. **PHP** — `FicheBilan.php` (classe principale, ~100 lignes) + 6 traits dans `Concerns/FicheBilan/`
2. **Blade** — `fiche-bilan.blade.php` (orchestrateur, ~55 lignes) + 10 partials dans `fiche-bilan/`

Ce découpage a été fait volontairement : à l'origine, tout tenait dans un seul fichier PHP de 1263 lignes et un seul blade de 1324 lignes. Le comportement n'a pas changé, seule l'organisation des fichiers.

### Pourquoi des traits et pas des classes séparées

`FicheBilan` est une page Livewire (héritée de `Filament\Resources\Pages\ViewRecord`, qui est elle-même un composant Livewire). Livewire a besoin que **toutes les propriétés publiques et méthodes appelées depuis la vue** (`wire:click`, `wire:model`, `#[Computed]`) vivent sur la classe du composant. On ne peut donc pas extraire la logique dans des services injectés classiquement — mais des **traits PHP** fonctionnent très bien : ils sont fusionnés dans la classe à la compilation, donc Livewire les voit exactement comme s'ils étaient écrits directement dans `FicheBilan`.

### Les 6 traits (`Concerns/FicheBilan/`)

| Trait | Responsabilité |
|---|---|
| `HasAccessControl` | `canAccess()` (qui a le droit de voir cette fiche : le marin lui-même, son mentor, ou quelqu'un avec la permission `fcmcentral::ficheBilan.voir`) |
| `HasNotes` | Commentaires/observations attachés au marin (modèle `Note`) |
| `HasJalonsEtGel` | Dates de jalons FCM (sédimentation, FCM, compétence), historique des gels, modale de modification des jalons |
| `HasSavoirFaireProgression` | Le cœur de la page : liste des savoir-faire avec leur % de progression, filtres (type/statut), statistiques agrégées |
| `HasPieceJointe` | Ajout/suppression d'un document PDF sur un savoir-faire **validé** |
| `HasDocumentsAnnexes` | Documents PDF libres attachés au marin, indépendants du parcours |

**Piège à connaître** : `supprimerFichierS3()` est défini une seule fois, dans `HasPieceJointe`, mais utilisé aussi par `HasDocumentsAnnexes`. Ça fonctionne parce que les deux traits sont combinés dans la même classe — mais si un jour `HasDocumentsAnnexes` est extrait dans un contexte où `HasPieceJointe` n'est pas `use`, ça cassera avec une erreur "Call to undefined method".

### Les 10 partials blade (`fiche-bilan/`)

| Fichier | Contenu |
|---|---|
| `styles.blade.php` | Tout le CSS de la page (classes `.sf-item`, `.section-card`, couleurs, dark mode) |
| `alerte-gel.blade.php` | Bannière orange si `$this->marin->estGele()` |
| `en-tete.blade.php` | Nom, grade, matricule, unité, mentors |
| `statistiques.blade.php` | Les 3 grilles de KPI (progression globale, par type S/C/EAD, par compétence) |
| `informations.blade.php` | Personnel / Équipe pédagogique / Jalons et historique des gels |
| `savoir-faire.blade.php` | La liste groupée par affectation, avec filtres et pièce jointe |
| `notes.blade.php` | Commentaires |
| `documents-annexes.blade.php` | Section documents libres |
| `objectifs-attente.blade.php` | Objectifs proposés en attente de validation |
| `modals.blade.php` | Les 3 modales (jalons, ajout pièce jointe, ajout document annexe) |

Blade partage automatiquement le scope de la vue parente avec `@include(...)` — aucune variable n'est passée explicitement, tout ce qui est accessible dans `fiche-bilan.blade.php` (via `$this->`) l'est aussi dans les partials.

---

## D'où viennent les données du parcours

**Il n'y a pas de table SQL avec une ligne par savoir-faire.** Le parcours entier d'un marin est stocké comme **un unique blob JSON** :

```
Marin
 └─ complementsFcm (FcmMarin, hasOne)
     └─ donnees_du_parcours (accesseur → MarinParcours, la ligne la plus récente)
         └─ parcoursmarin (colonne JSON, castée en array)
```

Structure du JSON (`parcoursmarin`) :

```
affectations[]
  └─ competences[]
       └─ competenceSavoirfaires[]   ← c'est ici qu'est le "savoir-faire" affiché
            ├─ uuid                  ← identifiant utilisé pour retrouver l'élément (csf_uuid)
            ├─ savoirfaire_type.code ← "S" (stage) / "C" (compagnonnage) / "EAD"
            ├─ etat_valid            ← bool, validé ou non
            ├─ date_validation, valideur, commentaire
            ├─ piece_jointe          ← chemin S3 (ajouté récemment, absent sur les vieux parcours)
            └─ objectifs[]           ← sous-éléments (pour le compagnonnage)
```

`HasSavoirFaireProgression::savoirFaires()` parcourt ces trois niveaux imbriqués à chaque affichage (pas de cache SQL) et aplatit tout en une `Collection` de tableaux associatifs, un par savoir-faire, en appliquant au passage les filtres actifs (type, statut).

**Conséquence importante** : modifier un savoir-faire (validation, pièce jointe...) ne se fait jamais avec une requête `UPDATE ... WHERE id = ...`. Il faut :
1. Charger tout le JSON (`$donneesDuParcours->parcoursmarin`)
2. Retrouver l'élément par son `uuid` en itérant les trois niveaux (voir `mutateCsfParUuid()` dans `HasPieceJointe`)
3. Modifier le tableau PHP en mémoire (par référence, avec `&`)
4. Réassigner tout le tableau à `$donneesDuParcours->parcoursmarin` et faire `->save()`

C'est ce que fait aussi le listener `ValidElementMarinParcoursListener` (dans `Modules/FcmCentral/app/Listeners/Save/MarinParcours/`) quand un mentor valide un élément depuis le livret — c'est lui qui écrit `etat_valid`, `valideur`, `commentaire`, et depuis peu `piece_jointe`.

---

## Pièces jointes sur les savoir-faire

### Règle métier

Une pièce jointe (PDF, 5 Mo max) ne peut être ajoutée ou supprimée **que sur un savoir-faire déjà validé** (`etat_valid === true`). Ce n'est pas qu'une contrainte d'affichage : `csfEstValide()` est revérifiée côté serveur dans chaque méthode (`ouvrirAjoutPieceJointe`, `supprimerPieceJointe`, `ajouterPieceJointe`) avant toute action.

### Il n'y a pas de "remplacer"

Volontairement simplifié : soit il n'y a pas de pièce jointe → bouton "Ajouter un document" ; soit il y en a une → uniquement "Supprimer". Pour changer de document, l'utilisateur doit supprimer puis ré-ajouter. Ça évite la logique de remplacement (suppression de l'ancien + upload du nouveau en une seule action, avec gestion d'échec partiel).

### Stockage

Disque `s3-public` (même disque que `ParcoursResource::livret_pdf`), classé par catégorie et date pour éviter un dossier unique avec des dizaines de milliers de fichiers :

```
fcmcentral/livretfcm/pieces-jointes/savoirfaire/{année}/{mois}/xxxxx.pdf
```

### Suppression vérifiée

`supprimerFichierS3()` ne se contente pas d'appeler `Storage::delete()` — elle vérifie **avant et après** que le fichier existe/n'existe plus :

```php
private function supprimerFichierS3(?string $chemin): bool
{
    if (!$chemin) return true;
    $disk = Storage::disk('s3-public');
    if (!$disk->exists($chemin)) return true;
    $disk->delete($chemin);
    return !$disk->exists($chemin);
}
```

Si la suppression échoue réellement (le fichier existe toujours après `delete()`), l'action s'arrête avec une notification "Échec de la suppression" — **la référence JSON n'est pas retirée**, pour ne jamais désynchroniser "il y a un lien dans l'UI" et "le fichier existe sur S3".

### Documents annexes (hors parcours)

Fonctionnalité voisine mais indépendante du parcours : des documents PDF libres attachés directement au marin (attestations, courriers...), stockés dans une colonne JSON dédiée `documents_annexes` sur `fcmcentral_fcm_marins` (migration `2026_07_23_000001_fcmcentral_add_documents_annexes_to_fcm_marins.php`). Contrairement aux pièces jointes de savoir-faire, il peut y en avoir un nombre illimité, sans condition de validation.

---

## Filtres de la liste des savoir-faire

Deux menus déroulants indépendants, combinés en `AND` :

- **Type** : Tous / Stages (`S`) / EAD / Objectifs-Compagnonnage (`C`)
- **Statut** : Tous / Validés / Non validés

Les deux sont appliqués dans `savoirFaires()` (`HasSavoirFaireProgression`), donc **tout ce qui dépend de cette collection** (statistiques, `csfEstValide()`, la liste affichée) reflète les filtres actifs. Attention si vous ajoutez une nouvelle fonctionnalité qui doit voir *tous* les savoir-faire indépendamment du filtre : il faudra requêter le JSON brut directement plutôt que passer par `$this->savoirFaires`.

---

## Pièges connus / points d'attention

1. **`csf_uuid` peut être `null` sur d'anciens parcours.** Le format historique identifiait un savoir-faire par une combinaison `savoirfaire_uuid + id` (voir `buildCsfPivotUuid()` dans `ValidElementMarinParcoursListener`) plutôt qu'un `uuid` direct sur le csf. Si `csf_uuid` est `null`, le bloc pièce jointe ne s'affiche pas du tout (`@if($savoirFaire['csf_uuid'] && ...)`).

2. **Bug d'environnement connu (pas dans ce code)** : sur cet environnement de dev, `APP_URL` contient déjà `/apps`, et les PanelProvider Filament ajoutent un second `/apps` par-dessus, ce qui casse les requêtes AJAX Livewire (double préfixe). Symptôme observé : une action Livewire (ex. suppression d'une pièce jointe) peut échouer à rafraîchir l'affichage même si elle a réussi côté serveur. À corriger dans `.env`, pas dans le code de cette page.

3. **`estGele()`** vient du modèle `Marin` (`Modules/FcmCentral/app/Models/Marin.php`) — vérifie l'existence d'un `Etat` sans `date_fin`. La bannière GEL de cette page réutilise cette même méthode (cohérent avec l'alerte GEL de `LivretDeFcmLight`).

4. **Les traits ne remplacent pas des tests.** Aucun test automatisé n'existe pour `FicheBilan` à ce jour (contrairement aux pages `Suivi*` dans `Modules/FcmCentral/tests/Feature/`). Si vous touchez à la logique de pièce jointe ou de filtres, il n'y a pas de filet de sécurité — testez manuellement dans le navigateur.

