# Permissions — Module FcmCentral

> **But de ce document** : référence complète des permissions disponibles dans FcmCentral, leur effet concret dans l'interface, et comment les combiner pour construire un rôle cohérent.

---

## Comment fonctionnent les permissions

Les permissions FcmCentral utilisent le système Spatie `laravel-permission`.

- Chaque permission est une chaîne préfixée `fcmcentral::`.
- Elles sont créées en base par le seeder : `php artisan db:seed --class="Modules\FcmCentral\Database\Seeders\PermissionsSeeder"`.
- On les attribue à un **rôle**, puis le rôle à un ou plusieurs **utilisateurs**.
- Les **Policies** (CRUD sur les modèles) génèrent automatiquement des permissions de la forme `fcmcentral::<modele>.<action>` (voir section dédiée plus bas).

---

## Permissions métier (définies dans le seeder)

Ces permissions contrôlent des **fonctionnalités spécifiques** de l'interface, indépendamment du CRUD sur les modèles.

### Accès général

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::voir_tous_les_marins` | Dans la liste des marins (`MarinResource`), lève la restriction du scope de la table Filament : l'utilisateur voit **tous** les marins en FCM, pas seulement ceux qu'il gère. |
| `rh::voir_tous_les_marins` | **Permission distincte de la précédente**, définie côté module `RH` (`MarinFiltreParUniteScope`) — s'applique globalement à **toute requête Eloquent sur `Marin`**, pas seulement à la liste Filament. Sans elle, un utilisateur ne voit que les marins de sa propre unité (ou uniquement lui-même s'il n'a pas d'unité). Voir la documentation du module RH pour le détail de cette logique de filtrage par unité. |
| `fcmcentral::recherche_annuaire` | Accès à la page **Recherche Annuaire** (recherche LDAP d'un marin pour l'intégrer en FCM). |
| `fcmcentral::change_configuration_module` | Accès à la page **Configuration** du module (paramètres globaux). Réservé administrateur. |
| `fcmcentral::voir_journal_des_evenements` | Accès à la ressource **Journal des événements** (StoredEvent). Affiche l'historique de toutes les actions traçées. |
| `fcmcentral::exporter_csv` | Bouton **Exporter CSV** visible dans la liste des marins. |

> Les deux permissions `voir_tous_les_marins` (`fcmcentral::` et `rh::`) sont indépendantes et **ne se remplacent pas l'une l'autre** — un utilisateur peut avoir besoin des deux pour un accès sans restriction (voir la liste ET requêter tous les marins ailleurs dans l'app).

### Gestion FCM d'un marin

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::marin.suivreEnFcm` | Bouton **Mettre en FCM / Retirer du FCM** visible dans la fiche marin et dans la recherche annuaire. Permet d'activer ou désactiver le suivi FCM d'un marin. |
| `fcmcentral::fcm.admin` | Donne accès aux actions avancées de la section FCM dans la fiche marin (jalons, mentors, cohorte, parcours). Agit comme un **verrou de niveau supérieur** : même si les permissions `attribuer.*` sont présentes, certaines sections ne s'affichent que si `fcm.admin` est aussi accordé. |
| `fcmcentral::definir_jalons_de_fcm` | Champs **Date de début / Date de fin FCM** (jalons) éditables dans la fiche marin. |
| `fcmcentral::attribuer.mentor` | Champ **Mentor(s)** visible et modifiable dans la fiche marin et la recherche annuaire. |
| `fcmcentral::attribuer.cohorte` | Champ **Cohorte** visible et modifiable dans la fiche marin et la recherche annuaire. |
| `fcmcentral::attribuer.parcours` | Champ **Parcours** visible et modifiable dans la fiche marin et la recherche annuaire. |

### Parcours

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::parcours.figer` | Bouton **Figer** visible dans la page d'édition d'un parcours (`EditParcours`) et dans la liste des parcours. Permet de créer une version sérialisée (snapshot figé). |

### Livret FCM

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::livret.voir` | Accès à la page **Livret FCM** d'un marin. Sans cette permission, la page renvoie une erreur d'autorisation. |
| `fcmcentral::livret.telecharger` | Bouton **Télécharger le livret PDF** visible dans la fiche marin. |
| `fcmcentral::livret.proposer` | Permet de proposer la validation d'un élément du parcours depuis le livret (`LivretFcmLight::canProposer()`). Sans permission, seul le marin lui-même peut proposer ses propres éléments. |
| `fcmcentral::livret.valider` | Permet de valider un élément (ou d'accepter une proposition) depuis le livret (`canValider()`). Sans permission, seul un mentor du marin peut valider. Réutilisée aussi pour gérer les pièces jointes du livret et de la fiche bilan (voir plus bas — pas de permission dédiée à l'écriture des pièces jointes). |
| `fcmcentral::livret.annuler` | Permet de revenir sur la validation d'un élément, ou d'annuler une proposition (`canAnnuler()`/`canDevalider()`). Sans permission, seul un mentor peut dévalider. |
| `fcmcentral::livret.refuser` | Permet de refuser une proposition de validation faite précédemment (`canRefuser()`). |

### Fiche Bilan

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::ficheBilan.voir` | Accès à la page **Fiche Bilan** d'un marin. |

### Pièces jointes et documents annexes (téléchargement)

Contrôlent l'accès en **lecture** aux fichiers privés (`s3-private`) via `PieceJointeDownloadController`/`DocumentAnnexeDownloadController` — voir `DOC_STOCKAGE_S3.md`. Sans ces permissions, un utilisateur peut quand même accéder aux fichiers d'un marin dont il est **le marin lui-même** ou **le mentor** (vérifié directement dans le contrôleur, indépendamment de toute permission Spatie).

| Permission | Effet |
|---|---|
| `fcmcentral::piece_jointe.voir` | Accès en téléchargement à la pièce jointe (savoir-faire/objectif) de **n'importe quel marin**, même sans lien (ni marin lui-même, ni mentor). Pensé pour les gestionnaires/admins FCM. |
| `fcmcentral::document_annexe.voir` | Même chose pour les documents annexes. **Permission distincte de `piece_jointe.voir`** — avoir l'une ne donne pas accès à l'autre. |

> Ces deux permissions ne contrôlent que la **lecture/téléchargement**. L'ajout/suppression d'une pièce jointe réutilise la permission existante `fcmcentral::livret.valider` (pas de permission dédiée à l'écriture à ce jour).

### Notes

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::note.voir` | Onglet **Notes** visible dans la fiche bilan. |
| `fcmcentral::note.creer` | Bouton **Créer une note** visible dans la fiche bilan et la fiche marin. |
| `fcmcentral::note.valider` | Bouton **Valider** visible dans le formulaire de note (marque la note comme officielle). |

### États (gel, jalons)

| Permission | Effet dans l'interface |
|---|---|
| `fcmcentral::etat.create` | Formulaire d'ajout d'un état (ex: gel) accessible dans la fiche bilan via `AddEtatForm`. |

---

## Permissions CRUD (générées par les Policies)

Chaque modèle dispose d'une `Policy` héritant de `GenericSkeletorPolicy`. Elle génère automatiquement les permissions suivantes selon le slug du modèle :

```
<slug>.index       → voir la liste (viewAny + view)
<slug>.create      → créer un enregistrement
<slug>.store       → alias create (accepté aussi)
<slug>.update      → modifier un enregistrement
<slug>.delete      → supprimer un enregistrement
<slug>.deleteAny   → supprimer en masse
<slug>.restore     → restaurer (soft delete)
<slug>.forceDelete → suppression définitive
<slug>.reorder     → réordonner
```

### Slugs par modèle

| Modèle | Slug de la Policy | Exemple de permission |
|---|---|---|
| `Marin` | `fcmcentral::marin` | `fcmcentral::marin.index` |
| `Parcours` | `fcmcentral::parcours` | `fcmcentral::parcours.create` |
| `ParcoursSerialise` | `fcmcentral::parcoursSerialise` | `fcmcentral::parcoursSerialise.index` |
| `ParcoursType` | `fcmcentral::parcoursType` | `fcmcentral::parcoursType.create` |
| `Affectation` | `fcmcentral::affectation` | `fcmcentral::affectation.update` |
| `Competence` | `fcmcentral::competence` | `fcmcentral::competence.index` |
| `CompetenceSavoirfaire` | `fcmcentral::competenceSavoirfaire` | `fcmcentral::competenceSavoirfaire.update` |
| `Savoirfaire` | `fcmcentral::savoirfaire` | `fcmcentral::savoirfaire.index` |
| `SavoirfaireType` | `fcmcentral::savoirfaireType` | `fcmcentral::savoirfaireType.create` |
| `Domaine` | `fcmcentral::domaine` | `fcmcentral::domaine.index` |
| `Cohorte` | `fcmcentral::cohorte` | `fcmcentral::cohorte.create` |
| `Etat` | `fcmcentral::etat` | `fcmcentral::etat.create` |
| `TypeEtat` | `fcmcentral::typeEtat` | `fcmcentral::typeEtat.index` |
| `Note` | `fcmcentral::note` | `fcmcentral::note.index` |
| `TypeNote` | `fcmcentral::typeNote` | `fcmcentral::typeNote.create` |
| `Objectif` | `fcmcentral::objectif` | `fcmcentral::objectif.update` |

> **Note** : `fcmcentral::marin.index` est utilisé explicitement dans `ListMarins` pour contrôler l'accès à la liste des marins.

---

## Rôles suggérés

### Rôle : `Consultant FCM` (lecture seule)

> Peut consulter les marins dont il est mentor ou dans sa cohorte, voir les livrets, télécharger les bilans.

```
fcmcentral::livret.voir
fcmcentral::livret.telecharger
fcmcentral::ficheBilan.voir
fcmcentral::note.voir
fcmcentral::marin.index
```

---

### Rôle : `Mentor`

> Peut voir ses marins supervisés, consulter les livrets, proposer des éléments du parcours, créer des notes.

```
fcmcentral::livret.voir
fcmcentral::livret.telecharger
fcmcentral::livret.proposer
fcmcentral::ficheBilan.voir
fcmcentral::note.voir
fcmcentral::note.creer
fcmcentral::marin.index
```

---

### Rôle : `Gestionnaire FCM`

> Gère les marins en FCM : mise en FCM, attribution mentors/cohorte/parcours, jalons, notes, états.

```
fcmcentral::voir_tous_les_marins
fcmcentral::recherche_annuaire
fcmcentral::marin.suivreEnFcm
fcmcentral::fcm.admin
fcmcentral::definir_jalons_de_fcm
fcmcentral::attribuer.mentor
fcmcentral::attribuer.cohorte
fcmcentral::attribuer.parcours
fcmcentral::livret.voir
fcmcentral::livret.telecharger
fcmcentral::livret.proposer
fcmcentral::livret.valider
fcmcentral::livret.annuler
fcmcentral::livret.refuser
fcmcentral::ficheBilan.voir
fcmcentral::note.voir
fcmcentral::note.creer
fcmcentral::note.valider
fcmcentral::etat.create
fcmcentral::exporter_csv
fcmcentral::marin.index
fcmcentral::piece_jointe.voir
fcmcentral::document_annexe.voir
```

---

### Rôle : `Administrateur FCM`

> Accès complet : tout ce que le Gestionnaire a, plus la configuration du module, la gestion des référentiels (parcours, domaines, cohortes…) et le journal des événements.

```
# Tout le rôle Gestionnaire FCM, plus :
fcmcentral::change_configuration_module
fcmcentral::voir_journal_des_evenements
fcmcentral::parcours.figer
fcmcentral::parcours.create
fcmcentral::parcours.update
fcmcentral::parcours.delete
fcmcentral::parcoursType.index
fcmcentral::parcoursType.create
fcmcentral::parcoursType.update
fcmcentral::parcoursType.delete
fcmcentral::cohorte.index
fcmcentral::cohorte.create
fcmcentral::cohorte.update
fcmcentral::cohorte.delete
fcmcentral::domaine.index
fcmcentral::domaine.create
fcmcentral::domaine.update
fcmcentral::affectation.index
fcmcentral::affectation.create
fcmcentral::affectation.update
fcmcentral::affectation.delete
fcmcentral::typeEtat.index
fcmcentral::typeEtat.create
fcmcentral::typeEtat.update
fcmcentral::typeNote.index
fcmcentral::typeNote.create
fcmcentral::typeNote.update
```

---
