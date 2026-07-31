# Procédures : Modules Laravel et Docker

---

## PARTIE 1 — Démarrer Docker

Le projet tourne dans Docker. Les conteneurs sont définis dans `docker/docker-compose.yml`.

### Lancer les conteneurs

```bash
cd /home/julienb/Documents/skeletor/docker
docker compose up -d
```

### Vérifier que les conteneurs tournent

```bash
docker compose ps
```

### Arrêter les conteneurs

```bash
docker compose down
```

### Accéder au shell du conteneur PHP

```bash
docker compose exec php bash
# puis dans le conteneur :
cd /app
```

### URLs de l'application (navigateur)

| Service      | URL                              |
|--------------|----------------------------------|
| Application  | http://localhost/                |
| PhpMyAdmin   | http://localhost/apps/pma/       |
| Code editor  | http://localhost/apps/code-editor/ |

Identifiants PhpMyAdmin :
- Serveur : `db`
- Utilisateur : `skeletoruser`
- Mot de passe : `skeletorpassword`

---

## PARTIE 2 — Installer un module existant depuis le serveur distant

### Étape 1 — Exporter depuis le serveur (terminal VSCode en ligne)

```bash
cd /chemin/vers/Modules
tar -czf NomDuModule.tar.gz NomDuModule/
```

Puis dans l'explorateur VSCode en ligne : **clic droit sur le `.tar.gz`** → **Download**
→ Sauvegarder dans `C:\Users\j.boisson\Documents\GPD\`

> `zip` n'est pas disponible sur le serveur, utiliser `tar` à la place.

### Étape 2 — Copier le fichier sur cet ordi

Déposer le `.tar.gz` dans `/home/julienb/Documents/skeletor/Modules/`, puis extraire :

```bash
tar -xzf NomDuModule.tar.gz -C /home/julienb/Documents/skeletor/Modules/
```

### Étape 3 — Activer le module dans modules_statuses.json

Ouvrir `/home/julienb/Documents/skeletor/modules_statuses.json` et ajouter le module :

```json
{
    "Planificationgpd": true,
    "NomDuModule": true
}
```

### Étape 4 — Régénérer l'autoloader (dans le conteneur PHP)

```bash
cd /home/julienb/Documents/skeletor/docker
docker compose exec php bash
# dans le conteneur :
cd /app
git config --global --add safe.directory /app
composer dump-autoload
```

> Si erreur `Permission denied` sur un fichier dans `vendor/` ou `public/`, quitter le conteneur
> et lancer depuis le terminal hôte :
> ```bash
> chmod o+w /home/julienb/Documents/skeletor/vendor/composer/*.php
> chmod o+w /home/julienb/Documents/skeletor/vendor/pest-plugins.json
> chmod -R o+w /home/julienb/Documents/skeletor/public/
> ```
> Puis relancer `composer dump-autoload` dans le conteneur.

### Étape 5 — Vérifier

Accéder à l'application dans le navigateur et vérifier qu'il n'y a plus d'erreur "Class not found".

---

## Notes importantes

- Le merge plugin est configuré pour inclure automatiquement `Modules/*/composer.json`
  → `composer dump-autoload` suffit, pas besoin de modifier le `composer.json` principal.
- Les fichiers `vendor/` sont owned par l'utilisateur hôte (`julienb`) mais le conteneur
  tourne en `fanlabuser` (UID 1111), d'où les problèmes de permissions récurrents.

