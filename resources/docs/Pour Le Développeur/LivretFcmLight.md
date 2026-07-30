# Livret FCM (light) — Documentation technique

> **But de ce document** : comprendre comment fonctionne ce composant pour pouvoir le reprendre sans tout relire ligne par ligne. Écrit pour quelqu'un qui découvre le code.

---

## À quoi sert ce composant

`LivretFcmLight` est le composant Livewire affiché sur la fiche d'un marin (dans FcmCentral et potentiellement d'autres modules qui l'intègrent — d'où le `#TODO` en haut du fichier sur les dépendances). Il affiche l'arborescence complète du parcours FCM d'un marin (affectations → compétences → savoir-faire → objectifs), permet de **proposer**, **valider**, **dévalider**, **refuser** un élément, et depuis peu d'y attacher une **pièce jointe PDF** au moment de la validation.

Il est **générique/paramétrable** : les classes `Marin`, `UserGeneratedEvent` et le préfixe de permission (`fcmcentral::`, etc.) lui sont passées en propriétés `#[Locked]` par le module appelant, plutôt que codées en dur — c'est ce qui permet à FcmCentral (et potentiellement FcmUnite) de le réutiliser tel quel.

---

## Architecture générale

Comme pour `FicheBilan` (module FcmCentral), le code est découpé en **traits PHP**, pas en services classiques : Livewire a besoin que toutes les propriétés publiques et méthodes appelées depuis la vue (`wire:click`, `wire:model`, `#[Computed]`) vivent sur la classe du composant. Les traits sont fusionnés dans la classe à la compilation, donc Livewire les voit exactement comme s'ils étaient écrits directement dans `LivretFcmLight`.

Avant refactorisation : un seul fichier de plus de 1100 lignes. Après : la classe principale ne fait plus qu'une centaine de lignes, le reste vit dans `Concerns/LivretFcmLight/`.

### Les 6 traits (`Concerns/LivretFcmLight/`)

| Trait | Responsabilité |
|---|---|
| `HasPermissions` | Tous les `can...()` : qui peut proposer/valider/dévalider/refuser, et les 4 états du marin (gelé, en sédimentation, en FCM, peut proposer/valider) |
| `HasParcoursData` | Chargement du marin et de son parcours brut (`marin()`, `parcours()`, `parcoursFiltre()`) |
| `HasFiltres` | Tout ce qui concerne le filtrage de l'arbre affiché : propriétés de filtre, `queryString` (filtres dans l'URL), listes disponibles pour les dropdowns, logique de filtrage récursive, calcul des statistiques |
| `HasPieceJointe` | Upload de pièce jointe PDF au moment de la validation (`stockerPieceJointe`), **et** gestion indépendante ajout/suppression sur un élément déjà validé (`ouvrirGestionPieceJointe`, `ajouterPieceJointeSurElement`, `supprimerPieceJointeSurElement`) — voir section dédiée |
| `HasValidationActions` | Les 4 actions métier : `proposer`, `valider`, `devalider`, `annulerProposition` — chacune dispatche un `UserGeneratedEvent` |
| `HasDisplayHelpers` | Nettoyage de texte pour tooltips/JS (échappement, accents, caractères spéciaux) |

La classe principale ne garde que : les propriétés `#[Locked]` (config injectée par le module appelant), `mount()`/`boot()` (actuellement vides), la synchronisation d'événements (`refreshElements`, `syncElements`, `afterElementAction`) et `render()`.

---

## D'où viennent les données

Comme pour `FicheBilan`, **il n'y a pas de table SQL avec une ligne par savoir-faire**. Le parcours entier est un blob JSON :

```
Marin
 └─ complements_fcm (accesseur)
     └─ donnees_du_parcours (accesseur → MarinParcours le plus récent)
         └─ parcoursmarin (colonne JSON castée en array)
```

`HasFiltres::appliquerFiltres()` reconstruit une copie **filtrée** de ce JSON à chaque affichage (recherche texte, statut, affectation, compétence, type de savoir-faire) — c'est `parcoursFiltre()` (dans `HasParcoursData`) qui appelle cette logique et que `render()` transmet à la vue.

**Aucune modification n'est faite directement dans ce composant.** Proposer/valider/dévaliser/refuser ne touchent jamais le JSON eux-mêmes : ils se contentent de dispatcher un `UserGeneratedEvent` (`ANNUL_ELMT_MARIN_PARCOURS`, `VALID_ELMT_MARIN_PARCOURS`, `PROPOS_ELMT_MARIN_PARCOURS`, `ANNUL_PROPOS_ELMT_MARIN_PARCOURS`). C'est un **listener côté module appelant** (ex. `ValidElementMarinParcoursListener` dans FcmCentral) qui reçoit l'événement et modifie réellement le JSON. Ce découpage événementiel est ce qui permet à ce composant "light" de rester agnostique du module qui l'utilise.

---

## Pièce jointe

Deux mécanismes distincts cohabitent, avec des rôles différents. Les deux stockent sur `s3-private` (jamais `s3-public` — voir `DOC_STOCKAGE_S3.md` dans `Modules/FcmCentral`).

### 1. Pièce jointe au moment de la validation

Quand un mentor valide un ou plusieurs éléments (`valider()`), il peut joindre un PDF (5 Mo max) qui sera attaché à **tous les éléments validés dans ce même appel**. Le fichier est stocké **avant** que les événements ne soient dispatchés :

```
valider()
  └─ stockerPieceJointe($elements_a_valider, $date)   ← upload sur S3, retourne le chemin (ou null)
       └─ createValidationEvent(..., $pieceJointePath) ← un événement par élément, avec le même chemin
```

Stockage : `fcmcentral/livretfcm/pieces-jointes/{savoirfaire|objectif}/{uuid_marin}/{année}/{mois}/xxxxx.pdf`.

**Point d'attention : qui écrit `piece_jointe` dans le JSON ?** Ce mécanisme transmet juste le chemin dans le `detail` de l'événement `VALID_ELMT_MARIN_PARCOURS`. C'est le **listener** du module appelant qui doit lire `detail['piece_jointe']` et l'écrire sur l'élément correspondant dans le JSON (voir `ValidElementMarinParcoursListener::updateElement()` côté FcmCentral). **Si un module intègre ce composant sans que son listener sache lire cette clé, la pièce jointe sera uploadée sur S3 mais jamais reliée à rien** — vérifiez que le listener cible gère bien `piece_jointe` avant de considérer la fonctionnalité opérationnelle pour un nouveau module.

### 2. Gestion après validation (ajout/suppression indépendante)

Contrairement au mécanisme ci-dessus (upload uniquement, au moment de la validation), ce second mécanisme permet d'ajouter ou de retirer une pièce jointe sur un élément **déjà validé**, à tout moment, depuis la modale "Infos" :

```
ouvrirGestionPieceJointe($type, $uuid)      ← ouvre la modale si canGererPieceJointe() ET elementEstValide()
  └─ ajouterPieceJointeSurElement()         ← upload + dispatch AJOUT_PIECE_JOINTE_MARIN_PARCOURS
  └─ supprimerPieceJointeSurElement($type, $uuid) ← dispatch SUPPR_PIECE_JOINTE_MARIN_PARCOURS (pas d'upload)
```

Comme pour `valider()`/`devalider()` (`HasValidationActions`), ce composant ne mute jamais le JSON lui-même : c'est `PieceJointeMarinParcoursListener` (module `FcmCentral`) qui reçoit l'événement, retrouve l'élément dans le JSON par `type`+`uuid`, écrit ou retire `piece_jointe`, et **nettoie le fichier S3 devenu orphelin** (ancien fichier remplacé, ou suppression). C'est le même listener que celui utilisé par `FicheBilan` pour sa propre gestion de pièce jointe — un seul point de mutation du JSON pour les deux composants.

**Condition d'accès** : `canGererPieceJointe()` (dans `HasPermissions`) — actuellement la même permission que la validation (`{PermissionPrefix}::livret.valider`), pas de permission dédiée à la gestion de pièce jointe côté Livewire.

### Téléchargement sécurisé

Comme pour `FicheBilan`, le lien affiché (`pieceJointeUrl`, calculé dans `item.blade.php`) n'est jamais une URL S3 directe : c'est une route applicative, résolue côté `FcmCentral` (`PieceJointeDownloadController`) même si l'upload a lieu ici, dans `FcmCommun` :

```php
route('fcmcentral.piece-jointe.telecharger', [
    'marinUuid' => $this->marin->uuid,
    'type' => $type,      // uniquement pour 'savoirfaire'/'objectif' — pas pour 'affectation'/'competence'
    'uuid' => $uuid,
])
```

Ce composant vit dans `FcmCommun`, mais **dépend directement d'une route nommée définie dans le module `FcmCentral`** (`fcmcentral.piece-jointe.telecharger`). Si `LivretFcmLight` est un jour intégré dans un module qui n'enregistre pas cette route (ex. un futur `FcmUnite` sans dépendance vers `FcmCentral`), le lien de téléchargement cassera silencieusement (`route()` lève une exception si le nom n'existe pas). À vérifier explicitement le jour où ce composant sera réellement partagé au-delà de `FcmCentral`.

### Ce composant ne supprime jamais de fichier directement

Ni le mécanisme 1 (validation), ni le mécanisme 2 (gestion après validation) ne suppriment eux-mêmes un fichier S3 — l'upload se fait ici, mais toute suppression physique passe systématiquement par `PieceJointeMarinParcoursListener` côté `FcmCentral`.

---

## Filtres

Quatre filtres combinables, tous préservés dans l'URL (`queryString`) pour pouvoir partager un lien filtré :

- **Recherche texte** (`search`) — sur le libellé/code d'un savoir-faire ou objectif
- **Statut** (`statutFilter`) — tous / validés / proposés / non validés
- **Affectation** (`affectationFilter`)
- **Compétence** (`competenceFilter`)
- **Type de savoir-faire** (`savoirfaireTypeFilter`) — S / C / EAD

La logique de filtrage (`HasFiltres`) est **récursive et reconstructive** : elle ne masque pas des lignes dans une table, elle reconstruit un nouveau tableau PHP à trois niveaux (`affectations` → `competences` → `competenceSavoirfaires` → `objectifs`) en ne gardant que ce qui correspond, niveau par niveau, en remontant (un parent est gardé si au moins un enfant correspond).

`savoirfairesAGriser()` / `objectifsAGriser()` sont un mécanisme d'affichage à part : plutôt que de masquer complètement un élément proposé-non-validé quand le filtre "non validés" est actif, on le grise (visuellement présent mais désactivé) — cette liste d'UUID à griser est calculée séparément et transmise à la vue.

---

## Permissions (`HasPermissions`)

Quatre états successifs déterminent si une action est possible sur un marin :

```
Gelé ? ──oui──> aucune action possible (proposer/valider/annuler/refuser tous à false)
  │
  non
  ↓
En sédimentation (date_sedimentation renseignée, date_fcm vide) ──> pas encore en FCM
  ↓
En FCM (date_sedimentation ET date_fcm renseignées, date_competence VIDE) ──> actions possibles
  ↓
date_competence renseignée ──> parcours terminé, marinEstEnFcm() redevient false
```

Sur cette base, chaque `can...()` applique en plus une règle de rôle :
- **Le marin lui-même** peut toujours proposer/annuler ses propres éléments, sans permission Spatie explicite
- **Un mentor** (`est_mentor_de()`) peut toujours valider/refuser, sans permission Spatie explicite
- **Sinon**, il faut la permission `{PermissionPrefix}::livret.xxx` (proposer/valider/annuler/refuser), où `PermissionPrefix` est injecté par le module appelant (ex. `fcmcentral`)
