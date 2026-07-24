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
| `HasPieceJointe` | Upload de la pièce jointe PDF (propriété `pieceJointe`, validation, stockage) |
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

## Pièce jointe à la validation

### Règle métier

Quand un mentor valide un ou plusieurs éléments (`valider()`), il peut joindre un PDF (5 Mo max) qui sera attaché à **tous les éléments validés dans ce même appel**. Le fichier est stocké **avant** que les événements ne soient dispatchés :

```
valider()
  └─ stockerPieceJointe($elements_a_valider, $date)   ← upload sur S3, retourne le chemin (ou null)
       └─ createValidationEvent(..., $pieceJointePath) ← un événement par élément, avec le même chemin
```

### Stockage

Disque `s3-public`, classé par catégorie (`savoirfaire` ou `objectif`, selon ce qui est présent dans le lot validé) et par année/mois pour éviter d'accumuler des dizaines de milliers de fichiers dans un seul dossier :

```
fcmcommun/livretfcm/pieces-jointes/{savoirfaire|objectif}/{année}/{mois}/xxxxx.pdf
```

### Ce composant ne supprime jamais de fichier

Contrairement à `FicheBilan` (côté FcmCentral) qui a une action dédiée de suppression avec vérification S3, `LivretFcmLight` ne fait qu'**ajouter** une pièce jointe au moment de la validation. La suppression/remplacement se gère ailleurs (page Fiche Bilan).

### Point d'attention : qui écrit `piece_jointe` dans le JSON ?

Ce composant transmet juste le chemin dans le `detail` de l'événement `VALID_ELMT_MARIN_PARCOURS`. C'est le **listener** du module appelant qui doit lire `detail['piece_jointe']` et l'écrire sur l'élément correspondant dans le JSON (voir `ValidElementMarinParcoursListener::updateElement()` côté FcmCentral). **Si un module intègre ce composant sans que son listener sache lire cette clé, la pièce jointe sera uploadée sur S3 mais jamais reliée à rien** — vérifiez que le listener cible gère bien `piece_jointe` avant de considérer la fonctionnalité opérationnelle pour un nouveau module.

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

---

## Pièges connus / points d'attention

1. **Ce composant est un contrat implicite avec ses listeners.** Il ne modifie jamais le parcours lui-même — tout passe par des `UserGeneratedEvent`. Si vous ajoutez un nouveau type d'action ici, il ne se passera rien tant qu'un listener côté module appelant ne sait pas le traiter.

2. **`stockerPieceJointe()` attache le même fichier à tous les éléments validés en un seul appel.** Si un mentor valide 3 savoir-faire d'un coup avec une seule pièce jointe, les 3 événements `VALID_ELMT_MARIN_PARCOURS` recevront le même chemin S3 dans leur `detail`. Ce n'est pas un bug — c'est voulu pour l'usage "un diplôme couvre plusieurs validations" — mais gardez ça en tête si vous devez un jour distinguer les pièces jointes par élément.

3. **`#TODO: vérifier les dépendances avec FcmCentral et FcmUnite`** (en haut du fichier, non résolu) — ce composant est censé être réutilisable par plusieurs modules mais son couplage réel (permissions, listeners attendus) n'a jamais été formellement vérifié pour FcmUnite.

4. **Aucun test automatisé** n'existe pour ce composant à ce jour. Si vous touchez aux filtres ou à la validation, testez manuellement dans le navigateur.
