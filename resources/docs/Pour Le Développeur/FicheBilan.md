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

**Historique d'un piège maintenant corrigé** : `supprimerFichierS3()` était défini une seule fois dans `HasPieceJointe`, mais utilisé aussi par `HasDocumentsAnnexes` en comptant sur le fait que les deux traits sont combinés dans la même classe. Lors du passage de `HasPieceJointe` au schéma event/listener, cette méthode a été supprimée du trait piège (elle n'était plus utilisée en interne) — ce qui a cassé silencieusement `HasDocumentsAnnexes::supprimerDocumentAnnexe()` ("Call to undefined method"). **Chaque trait définit maintenant sa propre copie locale de `supprimerFichierS3()`, self-contained.** Ne recomptez pas sur le partage implicite de méthodes privées entre traits d'une même classe — trop fragile aux refactors ultérieurs.

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
2. Retrouver l'élément par son `uuid` en itérant les trois niveaux
3. Modifier le tableau PHP en mémoire (par référence, avec `&`)
4. Réassigner tout le tableau à `$donneesDuParcours->parcoursmarin` et faire `->save()`

**`HasPieceJointe` ne fait plus cette mutation lui-même.** Depuis le passage au schéma event/listener (voir section dédiée plus bas), `ajouterPieceJointe()`/`supprimerPieceJointe()` se contentent d'uploader le fichier puis de dispatcher un `UserGeneratedEvent` (`AJOUT_PIECE_JOINTE_MARIN_PARCOURS` / `SUPPR_PIECE_JOINTE_MARIN_PARCOURS`). C'est `PieceJointeMarinParcoursListener` (`Modules/FcmCentral/app/Listeners/Save/MarinParcours/`) qui fait réellement les 4 étapes ci-dessus. Même schéma que `ValidElementMarinParcoursListener`, qui écrit `etat_valid`, `valideur`, `commentaire` quand un mentor valide un élément depuis le livret.

---

## Pièces jointes sur les savoir-faire

### Règle métier

Une pièce jointe (PDF, 5 Mo max) ne peut être ajoutée ou supprimée **que sur un savoir-faire déjà validé** (`etat_valid === true`). Ce n'est pas qu'une contrainte d'affichage : `csfEstValide()` est revérifiée côté serveur dans chaque méthode (`ouvrirAjoutPieceJointe`, `supprimerPieceJointe`, `ajouterPieceJointe`) avant toute action.

### Il n'y a pas de "remplacer"

Volontairement simplifié : soit il n'y a pas de pièce jointe → bouton "Ajouter un document" ; soit il y en a une → uniquement "Supprimer". Pour changer de document, l'utilisateur doit supprimer puis ré-ajouter. Ça évite la logique de remplacement (suppression de l'ancien + upload du nouveau en une seule action, avec gestion d'échec partiel).

### Stockage : `s3-private`, pas `s3-public`

Disque `s3-private` (voir `DOC_STOCKAGE_S3.md` à la racine du module pour le détail), classé par marin/année/mois pour éviter un dossier unique avec des dizaines de milliers de fichiers :

```
fcmcentral/livretfcm/pieces-jointes/savoirfaire/{uuid_marin}/{année}/{mois}/xxxxx.pdf
```

Aucune URL directe n'existe sur ce disque — voir "Téléchargement sécurisé" ci-dessous.

### Ajout/suppression : passe par un event/listener

`ajouterPieceJointe()` upload le fichier puis dispatche `UserGeneratedEvent(AJOUT_PIECE_JOINTE_MARIN_PARCOURS)` ; `supprimerPieceJointe()` dispatche `SUPPR_PIECE_JOINTE_MARIN_PARCOURS` (sans re-uploader). C'est `PieceJointeMarinParcoursListener` qui fait la mutation réelle du JSON **et** la suppression du fichier S3 devenu orphelin (ancien fichier remplacé, ou suppression demandée) — le trait `HasPieceJointe` ne touche plus jamais lui-même au fichier lors d'une suppression.

Ce même listener est aussi utilisé par `LivretFcmLight` (module `FcmCommun`) pour sa propre fonctionnalité d'ajout/suppression de pièce jointe après validation — un seul point de mutation du JSON pour les deux composants.

### Téléchargement sécurisé

Le lien affiché dans l'UI (`piece_jointe_url`, calculé dans `HasSavoirFaireProgression::savoirFaires()`) n'est **jamais** une URL S3 directe — c'est une route applicative :

```php
route('fcmcentral.piece-jointe.telecharger', [
    'marinUuid' => $this->marin->uuid,
    'type' => 'savoirfaire',
    'uuid' => $csf['uuid'] ?? '',
])
```

`PieceJointeDownloadController` vérifie que l'utilisateur connecté est le marin lui-même, un de ses mentors, ou détient la permission `fcmcentral::piece_jointe.voir`, avant de streamer le fichier depuis `s3-private`. Détail complet (autorisation, pièges) dans `DOC_STOCKAGE_S3.md`.

### Documents annexes (hors parcours)

Fonctionnalité voisine mais indépendante du parcours : des documents PDF libres attachés directement au marin (attestations, courriers...), stockés dans une colonne JSON dédiée `documents_annexes` sur `fcmcentral_fcm_marins` (migration `2026_07_23_000001_fcmcentral_add_documents_annexes_to_fcm_marins.php`). Contrairement aux pièces jointes de savoir-faire, il peut y en avoir un nombre illimité, sans condition de validation.

Même traitement que les pièces jointes : stockage sur `s3-private` (`fcmcentral/documents-annexes/marins/{marinId}/{année}/{mois}/`), et téléchargement via un contrôleur dédié `DocumentAnnexeDownloadController` (route `fcmcentral.document-annexe.telecharger`, permission `fcmcentral::document_annexe.voir`) — **une permission séparée de `piece_jointe.voir`**, les deux ne se substituent pas l'une à l'autre.

Contrairement aux pièces jointes, `ajouterDocumentAnnexe()`/`supprimerDocumentAnnexe()` mutent directement le tableau `documents_annexes` (pas d'event/listener ici — c'est un tableau plat sans validation métier associée, donc pas besoin du schéma event/listener utilisé pour le JSON du parcours).

`supprimerFichierS3()` (locale à `HasDocumentsAnnexes`, voir piège plus haut) vérifie **avant et après** que le fichier existe/n'existe plus, sur `s3-private` :

```php
private function supprimerFichierS3(?string $chemin): bool
{
    if (!$chemin) return true;
    $disk = Storage::disk('s3-private');
    if (!$disk->exists($chemin)) return true;
    $disk->delete($chemin);
    return !$disk->exists($chemin);
}
```

Si la suppression échoue réellement, l'action s'arrête avec une notification "Échec de la suppression" — **la référence JSON n'est pas retirée**, pour ne jamais désynchroniser "il y a un lien dans l'UI" et "le fichier existe sur S3".

---

## Filtres de la liste des savoir-faire

Deux menus déroulants indépendants, combinés en `AND` :

- **Type** : Tous / Stages (`S`) / EAD / Objectifs-Compagnonnage (`C`)
- **Statut** : Tous / Validés / Non validés

Les deux sont appliqués dans `savoirFaires()` (`HasSavoirFaireProgression`), donc **tout ce qui dépend de cette collection** (statistiques, `csfEstValide()`, la liste affichée) reflète les filtres actifs. Attention si vous ajoutez une nouvelle fonctionnalité qui doit voir *tous* les savoir-faire indépendamment du filtre : il faudra requêter le JSON brut directement plutôt que passer par `$this->savoirFaires`.

