# Blog EPSI — projet fil rouge Symfony

Support de TP du module **PHP / Symfony** (EPSI). Un seul projet du début à la fin :
un blog. Chaque TP ajoute une brique au même code.

| | |
|---|---|
| **Symfony** | 8.1 |
| **PHP** | 8.4 |
| **Base de données** | MySQL 8.4 |
| **Serveur web** | FrankenPHP (Caddy + PHP dans un seul processus) |
| **ORM** | Doctrine ORM 3 · **Templates** Twig 3 · **Dépendances** Composer 2 |

Vous n'installez **ni PHP, ni Composer, ni MySQL** sur votre poste : tout tourne dans
Docker. Seul prérequis : **Docker Desktop** (ou OrbStack) et **Git**.

---

## Démarrer en 4 commandes

```bash
git clone <url-de-votre-fork> blog && cd blog
docker compose up -d                          # construit et démarre les conteneurs
docker compose exec app composer install      # installe les dépendances PHP
docker compose exec app php bin/console doctrine:migrations:migrate --allow-no-migration
```

> `--allow-no-migration` évite une erreur tant qu'aucune migration n'existe :
> la première naît au TP 5.

Le site répond sur **<http://localhost:8000>**.

Dans `"8000:80"`, le port de gauche est celui de **votre machine**, celui de droite
celui **du conteneur**.

### Le port 8000 est déjà pris ?

Deux solutions :

```bash
APP_PORT=8080 docker compose up -d            # ponctuel
```

ou, de façon durable, créez un fichier `compose.override.yaml` (non versionné) :

```yaml
services:
  app:
    ports: !override ["8080:80"]
```

---

## Les commandes du quotidien

Toute commande PHP, Composer ou Symfony se lance **dans le conteneur**, jamais sur
votre poste :

```bash
docker compose exec app php bin/console <commande>
docker compose exec app composer <commande>
```

| Commande | Ce qu'elle fait |
|---|---|
| `docker compose up -d` | démarre les conteneurs en arrière-plan |
| `docker compose ps` | montre l'état des deux services |
| `docker compose logs -f app` | suit les logs — **à lire avant de demander de l'aide** |
| `docker compose down` | arrête tout, les données restent dans le volume |
| `docker compose down -v` | arrête tout **et supprime la base** |
| `docker compose exec app sh` | ouvre un shell dans le conteneur |

Un `Makefile` fournit les mêmes raccourcis : `make up`, `make install`, `make migrate`,
`make logs`, `make sh`, `make console c="debug:router"`. Tapez `make` pour la liste.

### Commandes Symfony utiles

```bash
docker compose exec app php bin/console debug:router        # toutes les routes
docker compose exec app php bin/console debug:autowiring    # services injectables
docker compose exec app php bin/console doctrine:schema:validate
docker compose exec app php bin/console cache:clear
```

---

## Le Profiler

En environnement `dev`, une **barre de debug** s'affiche en bas de chaque page. C'est
l'outil de diagnostic principal : route et controller qui ont répondu, requêtes SQL et
leur durée, templates rendus et variables transmises, temps d'exécution et mémoire.

**Réflexe : avant de demander de l'aide, ouvrez le Profiler et lisez l'erreur en
entier.** Le Profiler n'est jamais activé en production.

---

## Les 10 TP

| TP | Sujet | Durée | Ce que vous ajoutez au blog |
|---|---|---|---|
| 1 | Lancer l'environnement | 15 min | le projet tourne chez vous |
| 2 | Votre première page | 20 min | un controller, une route, un template |
| 3 | Un gabarit pour tout le site | 30 min | `base.html.twig` et l'héritage Twig |
| 4 | Routing et paramètres | 30 min | 8 routes, contraintes et conversions |
| 5 | L'entité `Article` | 30 min | une table créée depuis le code |
| 6 | Afficher les articles | 40 min | liste et détail, alimentés par des fixtures |
| 7 | Créer et éditer un article | 45 min | formulaire, validation, message flash |
| 8 | Extraire un service | 30 min | `SlugGenerator` injecté par autowiring |
| 9 | Inscription et connexion | 50 min | des comptes, et des articles signés |
| 10 | Recherche et commentaires | 1 h 30 | `QueryBuilder` et relations |

L'énoncé de chaque TP est dans [`tp/`](tp/).

---

## Structure du projet

```
bin/          bin/console, l'outil en ligne de commande
config/       configuration : routes, services, paquets
docker/       réglages PHP du conteneur
migrations/   migrations Doctrine (le schéma de la base, versionné)
public/       racine web — seul dossier exposé au navigateur
src/          tout le code PHP : Controller, Entity, Repository, Service
templates/    tous les templates Twig
tp/           les énoncés des TP
var/          fichiers générés : cache et logs (non versionnés)
vendor/       dépendances Composer — ne jamais modifier ni versionner
```

L'essentiel du travail se fait dans `src/`, `templates/` et `config/`.

---

## Configuration

`DATABASE_URL` est défini dans `.env`, versionné, avec les identifiants **locaux** du
conteneur MySQL :

```
DATABASE_URL="mysql://root:root@database:3306/app?serverVersion=8.4&charset=utf8mb4"
```

`database` est le **nom d'hôte** de la base, pas `localhost` : dans un réseau Docker,
un service s'appelle par son nom.

Pour surcharger quoi que ce soit sur votre poste, copiez `.env.local.dist` en
`.env.local` — ce fichier n'est jamais versionné. **Aucun identifiant réel ne doit
finir dans `.env`.**

---

## Quand ça ne marche pas

| Symptôme | Cause | Solution |
|---|---|---|
| `port is already allocated` | un autre projet occupe le port 8000 | `docker compose down` dans l'autre dossier, ou changez `APP_PORT` |
| `Connection refused` vers la base | la base n'avait pas fini de démarrer | relancez la commande — `docker compose ps` doit afficher `healthy` |
| Page blanche | une erreur PHP | `docker compose logs -f app` : l'erreur y est toujours |
| `Class not found` | namespace ou casse du nom de fichier | un fichier = une classe du même nom, le namespace suit les dossiers |
| Changement invisible | cache | `docker compose exec app php bin/console cache:clear` |

Pour repartir d'une base totalement vide :

```bash
docker compose down -v && docker compose up -d
docker compose exec app php bin/console doctrine:migrations:migrate --allow-no-migration
```

---

## Ce qui est attendu au rendu

- un dépôt Git avec des **commits réguliers et lisibles**, pas un seul commit final ;
- un `README` : versions, commandes d'installation, comptes de test ;
- `.env` versionné **sans identifiants réels**, `.env.local` ignoré ;
- les migrations dans `migrations/`, et des fixtures pour peupler la base.

Critères d'évaluation :

- le projet s'installe et démarre **à partir du README seul** ;
- routes nommées, **aucune URL codée en dur** dans les templates ;
- logique métier **hors des controllers** (services et repositories) ;
- formulaires validés côté serveur, mots de passe hashés ;
- aucune erreur ni avertissement dans le Profiler.

---

## Pour aller plus loin

- [symfony.com/doc](https://symfony.com/doc) — la documentation officielle
- [symfony.com/book](https://symfony.com/book) — *The Fast Track*, gratuit en ligne
- [twig.symfony.com](https://twig.symfony.com) · [doctrine-project.org](https://www.doctrine-project.org)
