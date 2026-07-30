# Stockage S3 (public / privé) — Documentation technique

> **But de ce document** : comprendre la différence entre les disques `s3-public` et `s3-private`, savoir lequel utiliser pour une nouvelle fonctionnalité, et comment fonctionne l'accès aux fichiers privés (contrôleur + route + autorisation). Écrit pour quelqu'un qui découvre le code.

---

## Les deux disques

Définis dans `config/filesystems.php` (racine du projet, pas dans le module) :

```php
's3-private' => [
    'driver' => 's3',
    'bucket' => env('AWS_PRIVATE_BUCKET', 'private-bucket'),
    // pas de clé 'url' : ce disque n'expose jamais d'URL publique directe
    ...
],

's3-public' => [
    'driver' => 's3',
    'bucket' => env('AWS_PUBLIC_BUCKET', 'public-bucket'),
    'url' => env('AWS_URL', ...),
    ...
],
```

| | `s3-public` | `s3-private` |
|---|---|---|
| Bucket | `AWS_PUBLIC_BUCKET` | `AWS_PRIVATE_BUCKET` |
| Accès | `Storage::disk('s3-public')->url($chemin)` génère une URL directe, accessible par n'importe qui qui la connaît (pas d'authentification) | Aucune URL publique. Le fichier ne peut être lu qu'en passant par un contrôleur Laravel qui vérifie les droits, puis stream le contenu |
| Usage typique | Assets publics, PDF de parcours déjà publics (`ParcoursResource::livret_pdf`) | Tout document personnel/sensible lié à un marin : pièces jointes de savoir-faire, documents annexes |

### Règle simple

**Dès qu'un fichier contient une donnée personnelle sur un marin (diplôme, justificatif, note...), il va sur `s3-private`.** `s3-public` ne doit servir qu'à des fichiers dont la fuite de l'URL ne poserait aucun problème (le simple fait de connaître le chemin S3 suffit à tout le monde pour le télécharger sur ce disque, il n'y a pas de contrôle d'accès dessus).

---

## Exemple légitime de `s3-public` : le livret mentor d'un `Parcours`

`Parcours::livret_pdf` (`Modules/FcmCentral/app/Filament/Fcmcentral/Resources/ParcoursResource.php`) est un cas où `s3-public` est le bon choix, à garder en tête comme référence :

```php
FileUpload::make('livret_pdf')
    ->label('Livret PDF')
    ->acceptedFileTypes(['application/pdf'])
    ->disk('s3-public')
    ->directory('fcmcentral/livretmentors')
    ->visibility('public')
    ->downloadable()
    ->openable()
```

**Pourquoi c'est légitime sur `s3-public`** : ce PDF est attaché à un `Parcours` — un **type/référentiel de formation** (ex. "DEASM"), pas à un marin précis. C'est un guide générique pour les mentors, potentiellement le même document pour des dizaines de marins suivant ce parcours. Il ne contient aucune donnée personnelle — sa fuite éventuelle n'expose rien de sensible. D'où l'absence de contrôleur dédié : `Parcours::getLivretPdfUrlAttribute()` génère directement `Storage::disk('s3-public')->url($this->livret_pdf)`, sans vérification d'accès.

Le modèle nettoie lui-même l'ancien fichier S3 quand `livret_pdf` change ou que le `Parcours` est supprimé (`static::updating()` / `static::deleting()` dans le boot du modèle) — même logique de nettoyage que les listeners de pièce jointe, mais en synchrone dans le modèle plutôt que dans un listener d'événement, puisqu'il n'y a pas de notion de "validation" à respecter ici.

**Ne pas confondre avec le livret PDF *d'un marin*** (bouton "Télécharger le livret PDF" dans la fiche marin, permission `fcmcentral::livret.telecharger`, `LivretPdfAction`) : celui-ci n'est **pas stocké sur S3 du tout** — il est généré à la volée à chaque téléchargement (`LivretPdfService::generatePdf()`, streamé directement via `response()->streamDownload()`). Pas de fichier persistant, donc pas de question de disque `s3-public`/`s3-private` à se poser pour celui-ci.

---

## Comment un fichier privé arrive sur S3

Rien de spécial : `UploadedFile::store($directory, 's3-private')`, comme pour n'importe quel disque Laravel. Exemple (`HasPieceJointe`, `FicheBilan`) :

```php
$directory = sprintf(
    'fcmcentral/livretfcm/pieces-jointes/savoirfaire/%s/%s/%s',
    $this->marin->uuid,
    now()->format('Y'),
    now()->format('m')
);
$chemin = $this->nouvellePieceJointe->store($directory, 's3-private');
```

Le chemin retourné (`$chemin`) est ce qu'on stocke en base (dans le JSON du parcours, ou dans `documents_annexes`) — **jamais une URL**, juste le chemin S3 relatif. C'est ce chemin qui sera relu plus tard par le contrôleur de téléchargement pour aller chercher le fichier.

### Convention de nommage des chemins

Tous les chemins commencent par `fcmcentral/` (même quand le code qui fait l'upload vit dans le module `FcmCommun`, voir `LivretFcmLight/HasPieceJointe.php`) — c'est une convention de regroupement dans le bucket, pas un lien avec l'emplacement du fichier PHP.

| Fonctionnalité | Chemin |
|---|---|
| Pièce jointe savoir-faire (FicheBilan) | `fcmcentral/livretfcm/pieces-jointes/savoirfaire/{uuid_marin}/{année}/{mois}/xxx.pdf` |
| Pièce jointe savoir-faire/objectif (LivretFcmLight) | `fcmcentral/livretfcm/pieces-jointes/{type}/{uuid_marin}/{année}/{mois}/xxx.pdf` |
| Document annexe | `fcmcentral/documents-annexes/marins/{marinId}/{année}/{mois}/xxx.pdf` |

---

## Comment un fichier privé est lu : contrôleur + route

Puisqu'il n'y a pas d'URL publique sur `s3-private`, il faut un point d'entrée applicatif qui : vérifie qui est connecté, vérifie que cette personne a le droit de voir *ce* fichier précis, puis stream le contenu depuis S3.

### Les deux contrôleurs existants

| Contrôleur | Route | Paramètres | Permission dédiée |
|---|---|---|---|
| `PieceJointeDownloadController` | `fcmcentral.piece-jointe.telecharger` | `{marinUuid}/{type}/{uuid}` (type = `savoirfaire` ou `objectif`, uuid = celui de l'élément dans le JSON du parcours) | `fcmcentral::piece_jointe.voir` |
| `DocumentAnnexeDownloadController` | `fcmcentral.document-annexe.telecharger` | `{marinUuid}/{documentUuid}` | `fcmcentral::document_annexe.voir` |

Route (`Modules/FcmCentral/routes/web.php`) :

```php
Route::middleware('auth')->get(
    '/fcmcentral/piece-jointe/{marinUuid}/{type}/{uuid}',
    PieceJointeDownloadController::class
)->name('fcmcentral.piece-jointe.telecharger');

Route::middleware('auth')->get(
    '/fcmcentral/document-annexe/{marinUuid}/{documentUuid}',
    DocumentAnnexeDownloadController::class
)->name('fcmcentral.document-annexe.telecharger');
```

`auth` suffit comme middleware (pas de vérification de permission au niveau de la route) : l'autorisation dépend du marin précis ciblé dans l'URL, pas d'une permission statique globale — elle est donc vérifiée **dans le contrôleur**, pas dans un middleware `can:`.

### Logique commune : `VerifieAccesMarinTrait`

Les deux contrôleurs partagent la même règle d'autorisation, factorisée dans `Modules/FcmCentral/app/Http/Controllers/Concerns/VerifieAccesMarinTrait.php` :

```php
private function marinEstAccessiblePour(Marin $marin, string $permission): bool
{
    if (auth()->user()?->can($permission)) return true;

    $marinConnecte = Marin::fromCurrentUser();
    if (!$marinConnecte) return false;

    $marinConnecte = cast_as_eloquent_descendant($marinConnecte, Marin::class);

    if ($marinConnecte->id === $marin->id) return true;

    return $marinConnecte->est_mentor_de($marin);
}
```

Autorisé pour, dans cet ordre :
1. **Un utilisateur avec la permission passée en paramètre** (`fcmcentral::piece_jointe.voir` ou `fcmcentral::document_annexe.voir`) — accès à n'importe quel marin, pensé pour les gestionnaires/admins.
2. **Le marin lui-même** — automatique, aucune permission requise.
3. **Un mentor de ce marin** (`est_mentor_de()`) — automatique aussi.

### Piège : le scope `MarinFiltreParUniteScope`

Les deux contrôleurs font systématiquement :

```php
$marin = Marin::withoutGlobalScope(MarinFiltreParUniteScope::class)
    ->where('uuid', $marinUuid)
    ->firstOrFail();
```

**Ne jamais retirer ce `withoutGlobalScope`.** Ce scope (module `RH`) filtre les marins visibles selon l'unité de l'utilisateur connecté. Sans le retirer ici, un mentor ou un gestionnaire situé dans une unité différente du marin ciblé ne pourrait même pas *trouver* le marin (→ `404` au lieu d'un `403` ou d'un accès légitime), puisque l'autorisation réelle est de toute façon revérifiée juste après par `marinEstAccessiblePour()`.

### Résolution du chemin : jamais fait confiance à l'URL

Le paramètre d'URL n'est **jamais** le chemin S3 lui-même — c'est un UUID métier (élément de parcours ou document). Le contrôleur va relire le chemin réel depuis la base :

- `PieceJointeDownloadController::trouverCheminPieceJointe()` parcourt le JSON `parcoursmarin` pour retrouver l'élément `savoirfaire`/`objectif` par son `uuid`, puis lit sa clé `piece_jointe`.
- `DocumentAnnexeDownloadController::trouverCheminDocumentAnnexe()` parcourt `documents_annexes` (tableau JSON sur `FcmMarin`) par `uuid`.

Ça évite qu'un utilisateur autorisé sur un marin puisse deviner/forcer un chemin S3 arbitraire dans l'URL pour accéder à un fichier d'un autre marin.

### Réponse

```php
return Storage::disk('s3-private')->response($chemin);
```

⚠️ Sur un disque S3, `->response()` renvoie un `Symfony\Component\HttpFoundation\StreamedResponse`, **pas** un `Illuminate\Http\Response`. Si vous typez le retour de la méthode, utilisez `Symfony\Component\HttpFoundation\Response` (la classe parente commune), sinon PHP lève une `TypeError` au runtime.

---

## Créer un nouveau type de fichier privé : checklist

1. Stocker avec `->store($directory, 's3-private')`, jamais `'s3-public'`.
2. Ne jamais construire d'URL S3 directe dans une vue Blade (`Storage::disk('s3-private')->url(...)` n'a pas de sens sur ce disque). Toujours passer par `route('...')` vers un contrôleur dédié.
3. Créer un contrôleur `__invoke` qui `use VerifieAccesMarinTrait;`, retire le scope `MarinFiltreParUniteScope` sur la recherche du marin, vérifie l'accès via `marinEstAccessiblePour()`, résout le chemin réel depuis la base (jamais depuis l'URL), puis `abort_if`/`abort_unless` pour les cas 403/404.
4. Ajouter une permission dédiée dans `PermissionsSeeder.php` si l'accès "gestionnaire, sans lien avec le marin" doit être possible.
5. Ajouter la route dans `routes/web.php` avec le middleware `auth`.

---


