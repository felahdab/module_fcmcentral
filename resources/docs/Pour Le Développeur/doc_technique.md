# Documentation Technique — Module FcmCentral

> **But de ce document** : référence technique pour éviter les doublons de fonctions et comprendre ce qui existe déjà avant d'en créer de nouvelles.

---

## Architecture des modèles

```
RH\Models\Marin (base, table rh_marins)
    └── FcmCommun\Models\Marin
            └── FcmCentral\Models\Marin        ← modèle principal côté marin

FcmCommun\Models\FcmMarin (base)
    └── FcmCentral\Models\FcmMarin             ← données FCM d'un marin (1 ligne par marin en FCM)

FcmCommun\Models\MarinParcours (base)
    └── FcmCentral\Models\MarinParcours        ← affectation marin ↔ parcours sérialisé

FcmCommun\Models\ParcoursSerialise (base)
    └── FcmCentral\Models\ParcoursSerialise    ← version figée d'un parcours
```

**Règle importante** : `Marin` contient les infos RH + méthodes FCM de haut niveau. `FcmMarin` contient les données FCM brutes (cohorte, mentors, taux, gel, jalons).

---

## Tables et préfixes

| Table | Modèle |
|---|---|
| `rh_marins` | `RH\Models\Marin` / `FcmCentral\Models\Marin` |
| `fcmcentral_fcm_marins` | `FcmCentral\Models\FcmMarin` |
| `fcmcentral_fcm_marin_mentors` | table pivot mentors (fcm_marin_id, marin_id) |
| `fcmcentral_marin_parcours` | `FcmCentral\Models\MarinParcours` |
| `fcmcentral_parcours_serialises` | `FcmCentral\Models\ParcoursSerialise` |
| `fcmcentral_parcours` | `FcmCentral\Models\Parcours` |
| `fcmcentral_affectations` | `FcmCentral\Models\Affectation` |
| `fcmcentral_competences` | `FcmCentral\Models\Competence` |
| `fcmcentral_competence_savoirfaires` | `FcmCentral\Models\CompetenceSavoirfaire` |
| `fcmcentral_objectifs` | `FcmCentral\Models\Objectif` |

---

## Modèle `FcmMarin`

Représente **les données FCM d'un marin** (1 enregistrement = 1 marin suivi en FCM).

### Relations

| Méthode | Type | Cible | Utilisée |
|---|---|---|---|
| `marin()` | `belongsTo` | `Marin` via `rh_marin_id` | ✅ oui |
| `cohorte()` | `belongsTo` | `Cohorte` | ✅ oui |
| `mentors()` | `belongsToMany` | `Marin` via `fcmcentral_fcm_marin_mentors` | ✅ oui |
| `parcoursSerialises()` | `belongsToMany` | `ParcoursSerialise` via `fcmcentral_marin_parcours` | ✅ oui |
| `lignesDansMarinParcours()` | `hasMany` | `MarinParcours` | ✅ via `donnees_du_parcours` |

### Attributs calculés

| Attribut | Retourne | Utilisé |
|---|---|---|
| `$fcmMarin->donnees_du_parcours` | `?MarinParcours` — première ligne de parcours | ✅ oui |

### Méthodes métier — **utilisées**

```php
$fcmMarin->hasParcours(): bool          // a un parcours sérialisé attribué
$fcmMarin->getJalonFcmLibelle(): ?string // 'SED', 'FCM', 'FIN' selon les dates jalons
```

### Méthodes métier — **non utilisées dans le code applicatif**

> Ces méthodes existent mais ne sont appelées nulle part (hors tests). À garder si besoin futur, à supprimer si nettoyage souhaité.

```php
$fcmMarin->hasMentor(): bool            // vérifie si au moins un mentor assigné
$fcmMarin->hasCohorte(): bool           // vérifie si cohorte assignée
$fcmMarin->getTauxGlobal(): ?float      // taux global depuis donnees_du_parcours
$fcmMarin->getTauxStage(): ?float
$fcmMarin->getTauxActivite(): ?float
$fcmMarin->getInfosFcm(): array         // résumé complet sous forme de tableau
$fcmMarin->assignerCohorte(Cohorte)     // assigne une cohorte et save
$fcmMarin->retirerCohorte()
$fcmMarin->assignerMentor(Marin|RHMarin) // ajoute un mentor (syncWithoutDetaching)
$fcmMarin->retirerMentor(Marin|RHMarin) // détache un mentor
$fcmMarin->retirerTousLesMentors()
$fcmMarin->setCohorteAttribute(Cohorte) // setter Eloquent → appelle assignerCohorte()
```

### Scopes — **non utilisés**

```php
FcmMarin::cohorte($cohorte)       // filtre par cohorte_id
FcmMarin::avecMentor($mentor)     // whereHas('mentors', where marin_id)
FcmMarin::sansMentor()            // doesntHave('mentors')
FcmMarin::sansCohorte()           // whereNull('cohorte_id')
```

---

## Modèle `Marin` (FcmCentral)

Étend `FcmCommun\Marin` qui étend `RH\Marin`. Contient les méthodes FCM de haut niveau.

> ⚠️ Ce modèle est **volumineux** (819 lignes). Beaucoup de méthodes ont une documentation interne détaillée mais sont peu utilisées en pratique.

### Relations

| Méthode | Retourne | Utilisée |
|---|---|---|
| `complementsFcm()` | `hasOne(FcmMarin)` — données FCM (1ère ligne) | ✅ massivement |
| `lignes_dans_fcm_marin()` | `hasMany(FcmMarin)` — toutes les lignes FCM | ⚠️ via `complements_fcm` attribut |
| `etats()` | `hasMany(Etat)` | ✅ oui |

### Attributs calculés — **utilisés**

```php
$marin->suivi_en_fcm        // bool — est en FCM (getSuiviEnFcmAttribute)
$marin->complements_fcm     // ?FcmMarin — accès via lignes_dans_fcm_marin->first()
$marin->est_mentor          // bool — getEstMentorAttribute (alias de isMentor())
$marin->full_name_and_grade // string — grade + prénom + nom
$marin->marins_mentores     // Collection<Marin> — marins dont ce marin est mentor
$marin->parcours            // ?Parcours — accès direct au Parcours via compléments FCM
```

### Méthodes métier — **utilisées**

```php
$marin->estEnFcm(): bool                       // alias de complementsFcm()->exists()
$marin->est_mentor_de(Marin $autre): bool      // ce marin est-il mentor de $autre ?
$marin->estGele(): bool                        // a un gel actif (date_fin null)
$marin->gelActif(): ?Etat                      // récupère le gel actif
$marin->historiserEtat(string $code, ...): void // crée un enregistrement Etat
$marin->getDernierTypeEtat()                   // dernier Etat
$marin->getDernierTypeEtatCode(): ?string
$marin->getDernierTypeEtatLibelle(): ?string
$marin->getDernierTypeEtatId(): ?int
```

### Méthodes métier — **non utilisées dans le code applicatif**

```php
$marin->isMentor(): bool               // FcmMarin::whereHas('mentors')... même chose que est_mentor
$marin->getGrade(): ?string            // grade->libelle_court ?? libelle_long
$marin->getUniteAsString(): ?string    // unite->libelle_court ?? libelle_long
$marin->getCohorte(): ?Cohorte         // complementsFcm?->cohorte
$marin->getMentors(): Collection       // complementsFcm?->mentors ?? collect()
$marin->marinsSupervises(): Collection // Marin::mentorePar($this)->get()
$marin->nombreMarinsSupervises(): int  // compte FcmMarin whereHas mentors
$marin->getInfosResume(): array        // résumé complet

Marin::estMentor(?Model $m): bool      // méthode statique (doublon de est_mentor)
```

### Scopes — **utilisés**

```php
Marin::cohorte($cohorte)      // whereIn(id, fcm_marins where cohorte_id)
Marin::mentorePar($marin)     // marins dont $marin est le mentor (via pivot)
Marin::mentor()               // marins présents dans fcmcentral_fcm_marin_mentors
Marin::enFcm()                // whereHas('complementsFcm')
```

### Scopes — **non utilisés**

```php
Marin::pasEnFcm()             // whereNotIn(id, fcm_marins)
Marin::avecGrade(string)      // whereHas('grade', where libelle)
Marin::dansUnite(string)      // whereHas('unite', where libelle LIKE)
```

---

## Modèle `MarinParcours`

Pivot entre `FcmMarin` et `ParcoursSerialise`. Stocke la structure JSON du parcours marin (`parcoursmarin`) et les taux.

### Relations

| Méthode | Retourne | Utilisée |
|---|---|---|
| `fcmMarin()` | `belongsTo(FcmMarin)` via `fcmmarin_id` | ✅ oui |
| `parcours_serialise()` | `belongsTo(ParcoursSerialise)` via `parcoursserialise_id` | ✅ oui |
| `marin()` | `hasOneThrough(Marin, FcmMarin)` | ✅ oui |
| `ligne_dans_fcm_marins()` | `belongsTo(FcmMarin)` (doublon de `fcmMarin()`) | ⚠️ doublon |

---

## Modèle `ParcoursSerialise`

Version figée d'un parcours. Contient la structure JSON complète dans `parcours`.

### Relations

| Méthode | Retourne | Utilisée |
|---|---|---|
| `fcmMarins()` | `belongsToMany(FcmMarin)` via `fcmcentral_marin_parcours` | ✅ oui |
| `parcoursModel()` | `belongsTo(Parcours)` via `parcours_id` | ✅ oui |
| `marins()` | `belongsToMany(Marin)` — **incomplète/commentée** | ⚠️ à supprimer |
| `marinparcours()` | `hasMany(MarinParcours)` | ❌ non utilisée |

> **Note** : `marins()` utilise un `foreignPivotKey` incorrect (`'parcours_id'` au lieu de `'parcoursserialise_id'`). Elle est marquée "À SUPP" dans le code. Ne pas l'utiliser.

---

## Modèle `Parcours`

### Relations — toutes utilisées

```php
$parcours->affectations()          // belongsToMany(Affectation) via affectation_parcours
$parcours->competences()           // hasMany(Competence) filtrées par type et affectations
$parcours->competenceSavoirfaires() // hasMany(CompetenceSavoirfaire) filtrées
$parcours->parcoursSerialise()     // hasMany(ParcoursSerialise)
$parcours->parcoursType()          // belongsTo(ParcoursType)
```

### Méthodes

```php
$parcours->getLatestSerializedVersion(): int  // version max des ParcoursSerialise
$parcours->livret_pdf_url: ?string            // URL Storage du PDF (❌ non utilisé en dehors de la vue)
```

---

## Doublons identifiés

| Doublon | Explication |
|---|---|
| `Marin::isMentor()` et `$marin->est_mentor` | Font la même chose — utiliser l'attribut `est_mentor` |
| `Marin::estMentor(?Model)` (statique) et `$marin->est_mentor` | Doublon — utiliser l'attribut |
| `MarinParcours::ligne_dans_fcm_marins()` et `MarinParcours::fcmMarin()` | Même relation, noms différents — utiliser `fcmMarin()` |
| `Marin::getMentors()` et `$marin->complementsFcm?->mentors` | Même résultat — utiliser la relation directement |
| `Marin::getCohorte()` et `$marin->complementsFcm?->cohorte` | Même résultat |
| `Marin::nombreMarinsSupervises()` et `FcmMarin::whereHas('mentors')` direct | Fonction utilitaire peu utilisée |

---

## Méthodes à supprimer (candidates)

> Vérifiez qu'elles ne sont pas appelées dans des modules externes avant suppression.

### `FcmMarin`
- `getInfosFcm()` — résumé tableau jamais utilisé
- `setCohorteAttribute()` — setter Eloquent dangereux (side-effect silencieux)

### `Marin`
- `isMentor()` → remplacé par `$marin->est_mentor`
- `estMentor(?Model)` (statique) → doublon
- `getInfosResume()` → jamais appelé
- `scopePasEnFcm()` → jamais utilisé
- `scopeAvecGrade()` → jamais utilisé
- `scopeDansUnite()` → jamais utilisé
- `historiqueGels()` → jamais utilisé
- `getMentors()` / `getCohorte()` / `getGrade()` / `getUniteAsString()` → accès direct préférable
- `marinsSupervises()` → utiliser `Marin::mentorePar($this)->get()` directement

### `ParcoursSerialise`
- `marins()` → relation cassée, marquée À SUPPRIMER dans le code
- `marinparcours()` → jamais utilisée (utiliser `fcmMarins()`)

### `MarinParcours`
- `ligne_dans_fcm_marins()` → doublon de `fcmMarin()`

---

## Accès rapide aux données courantes

### Savoir si un marin est en FCM
```php
$marin->suivi_en_fcm           // attribut (bool)
$marin->estEnFcm()             // méthode (bool) — même chose
$marin->complementsFcm         // accès à FcmMarin (null si pas en FCM)
```

### Mentors d'un marin
```php
$marin->complementsFcm?->mentors           // Collection<Marin> via pivot
$marin->getMentors()                       // idem (wrapper)
```

### Marins supervisés par un mentor
```php
$marin->marins_mentores                    // attribut Collection<Marin>
Marin::mentorePar($mentor)->get()          // scope SQL (plus efficace pour grandes listes)
$marin->nombreMarinsSupervises()           // count uniquement
```

### Parcours d'un marin
```php
$marin->parcours                           // ?Parcours — accès direct
$marin->complementsFcm?->donnees_du_parcours  // ?MarinParcours — avec taux et JSON
$marin->complementsFcm?->donnees_du_parcours?->parcours_serialise  // ?ParcoursSerialise
```

### Cohorte d'un marin
```php
$marin->complementsFcm?->cohorte           // relation Eloquent (recommandé)
$marin->getCohorte()                       // wrapper (même résultat)
```

### Taux de progression
```php
$marin->complementsFcm?->donnees_du_parcours?->taux_global   // float|null
$marin->complementsFcm?->getTauxGlobal()                     // même chose via FcmMarin
```

### Vérifier si mentor
```php
$marin->est_mentor             // attribut bool (recommandé)
$marin->isMentor()             // méthode (doublon)
$marin->est_mentor_de($autre)  // ce marin supervise-t-il $autre ?
```

---

## Événements disponibles (`UserGeneratedEventTypes`)

| Enum | Description |
|---|---|
| `MARIN_EN_FCM` | Mise en FCM d'un marin |
| `RETRAIT_FCM` | Retrait du suivi FCM |
| `CREATION_PARCOURS_SERIALISE` | Figer un parcours (créer une version) |
| `ATTRIBUER_PARCOURS_SERIALISE_MARIN` | Affecter un parcours sérialisé à un marin |
| `ATTRIBUER_MENTOR_MARIN` | Assigner un/des mentors à un marin — payload : `['mentors' => [uuid, ...]]` |
| `ATTRIBUER_COHORTE_MARIN` | Assigner une cohorte à un marin |
| `PROPOS_ELMT_MARIN_PARCOURS` | Proposer un élément (SF, compétence...) |
| `VALID_ELMT_MARIN_PARCOURS` | Valider un élément |
| `ANNUL_ELMT_MARIN_PARCOURS` | Annuler une validation |
| `ANNUL_PROPOS_ELMT_MARIN_PARCOURS` | Annuler une proposition |

**Format d'appel :**
```php
event(new UserGeneratedEvent(
    event_type: UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
    user_uuid: auth()->user()->uuid,
    object_class: ObjectClasses::MARIN,
    object_uuid: $marin->uuid,
    detail: ['mentors' => [$mentor->uuid]]   // tableau d'UUIDs depuis refactoring many-to-many
));
```

---

## Notes importantes

- **`complementsFcm` vs `complements_fcm`** : `complementsFcm()` est la *relation* Eloquent (`hasOne`). `complements_fcm` est l'*attribut calculé* (`getComplementsFcmAttribute` → `lignes_dans_fcm_marin->first()`). Préférer `complementsFcm` (relation Eloquent, eager-loadable).

- **Mentors** : depuis la migration many-to-many (juin 2026), `mentor_id` n'existe plus sur `fcmcentral_fcm_marins`. Les mentors sont dans la table pivot `fcmcentral_fcm_marin_mentors`. Le payload des événements utilise `mentors` (pluriel, tableau d'UUIDs).

- **`ParcoursSerialise::parcoursModel()`** : nommé ainsi (pas `parcours()`) pour éviter un conflit avec le champ JSON `parcours` stocké dans la table.

- **`MarinParcours::parcoursmarin`** : champ JSON (colonne `longText`) contenant la structure complète du parcours marin avec tous les états de validation.


