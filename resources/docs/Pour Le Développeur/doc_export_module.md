# Procédure : Exporter et installer un module Laravel

## Contexte

Le projet tourne dans Docker. Le code source est dans `Modules/`. Quand PHP n'est pas disponible directement en local, on manipule les fichiers manuellement.

---

## 1. Exporter un module depuis le serveur distant (VSCode en ligne)

Dans le terminal du VSCode en ligne :

```bash
cd /chemin/vers/Modules
tar -czf NomDuModule.tar.gz NomDuModule/
```

Puis dans l'explorateur VSCode : **clic droit sur le `.tar.gz`** → **Download**.

---

## 2. Installer le module sur la machine locale

Copier le `.tar.gz` dans le dossier `Modules/` du projet, puis extraire :

```bash
tar -xzf NomDuModule.tar.gz -C /home/julienb/Documents/skeletor/Modules/
```

---

## 3. Activer le module

### Option A — Via artisan (si PHP/Docker disponible)

```bash
php artisan module:enable NomDuModule
```

### Option B — Manuellement (si PHP non disponible)

Éditer ou créer le fichier `modules_statuses.json` à la racine du projet :

```json
{
    "NomDuModule": true
}
```

Si d'autres modules sont déjà listés, ajouter simplement la ligne correspondante.

---

## 4. Vérifier

```bash
php artisan module:list
```

Ou vérifier que `modules_statuses.json` contient bien `"NomDuModule": true`.
