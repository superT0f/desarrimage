# AGENTS.md — Désarrimage

Référence pour les personnes et les agents IA qui contribuent au projet. Le but : avancer vite sans faire semblant d’avoir vérifié ce qui ne l’a pas été.

## ⚓ Boussole

Désarrimage documente des alternatives numériques françaises et européennes et, surtout, les modes opératoires pour migrer depuis des outils extra-européens.

- Site : https://desarrimage.prigent.tech
- Dépôt : https://github.com/superT0f/desarrimage
- Version courante : lire `.version`, jamais la deviner.
- Langue du produit, des données et de la documentation : français.
- Code : AGPL-3.0-or-later ; données et visuels : CC BY-SA 4.0.

## 🧑‍✈️ Collaboration humain–IA, sans enfumage

Le projet est construit en **pair-programming humain–IA** :

- **Christophe Prigent / SuperT0f** porte la vision, choisit les priorités, arbitre les décisions, fournit les accès et valide les livraisons.
- **Kimi K3.1 Pro assist** intervient comme assistant technique : exploration, propositions, implémentation, SVG, documentation, Docker, SemVer, changelog, tests et déploiements demandés.

La méthode est itérative : discuter du besoin, proposer une approche, implémenter, exécuter le scénario réel quand c’est possible, montrer le résultat, corriger, puis committer avec l’accord du mainteneur.

Règles d’honnêteté :

- Ne jamais présenter une proposition de code comme vérifiée avant d’avoir exécuté les contrôles adaptés.
- Une fiche marquée `verified_at: null` est un **contenu d’amorçage**, pas une migration réellement testée.
- Un appel API ou un build réussi ne prouve pas qu’une migration métier a été effectuée.
- Ne pas inventer de source, de tarif, de pays d’origine ou de retour d’expérience.
- Expliciter les blocages et les parties non vérifiées au lieu de les masquer.
- L’accès humain aux identifiants ne dispense jamais de protéger les secrets.

## 🧱 Architecture

- `api/` : PHP 8.4, Slim 4, PDO, Phinx, MariaDB/MySQL.
- `client/` : Vue 3, JavaScript, Vite 5, Pinia, Vue Router, Axios et CSS global.
- `data/catalog.json` : catalogue contributif importé par un seed idempotent.
- `data/guides/` : guides riches séparés, importés dans des tables normalisées (critères, valeurs, coûts, risques, phases et sources) par `GuideSeeder` et exposés par `GuideRepository`.
- `client/public/catalog-snapshot.json` : instantané généré pour la home, à ne pas éditer à la main.
- `Dockerfile` : targets `api` et `client`, images qualifiées avec `docker.io/library/...` pour rester compatibles Podman.
- `compose.yaml` : front, API et MariaDB avec volumes persistants.
- `api/openapi.yaml` : contrat public de l’API, servi sur `/api/openapi.yaml` et affiché par `/swagger.html` via Swagger UI auto-hébergé.
- `Makefile` : développement, contrôles, releases et déploiement Gandi.
- `scripts/` : synchronisation SemVer et génération du changelog.
- `docs/releases/` : notes techniques et marketing par version ; `docs/images/` contient leurs captures réelles.

## 🐳 Développement

La voie normale est Docker :

```bash
make dev
make dev-ps
make dev-logs
make dev-down
```

- Front : http://localhost:5173
- API : http://localhost:8080/api/health
- MariaDB : `127.0.0.1:3307`
- Credentials de développement jetables : `desarrimage` / `desarrimage-dev`.

`make dev` choisit Docker Compose en priorité, puis Podman. Si nécessaire, `make compose-bootstrap` installe `podman-compose` dans `.tmp/`, sans `sudo` ni installation globale.

Environnement Podman réellement validé le 16 novembre 2026 : **Podman 4.9.3 + podman-compose 1.6.0**. Mettre à jour cette référence après tout nouveau test de version.

L’entrypoint API attend MariaDB, applique les migrations et lance le seed à chaque démarrage. Les sources sont montées avec hot-reload. Ne pas supprimer les volumes sans demande explicite.

Le développement hors Docker reste disponible avec `make install`, `make db-migrate`, `make db-seed`, `make dev-api` et `make dev-client`.

## ✅ Contrôles avant livraison

Toujours lancer :

```bash
make check
make docker-check
```

`make check` valide Composer, la syntaxe PHP, le catalogue JSON, la synchronisation SemVer et le build Vite. Après un changement Docker ou d’API, démarrer la stack et tester de vrais appels HTTP, pas seulement les imports ou la compilation.

Contrôles utiles :

```bash
curl --fail http://localhost:8080/api/health
curl --fail http://localhost:8080/api/stats
curl --fail 'http://localhost:8080/api/products?search=nextcloud'
curl --fail http://localhost:8080/api/products/google-drive
```

La CI GitHub Actions reconstruit aussi les images et effectue un smoke test de la stack. Ne pas afficher un badge de succès pour un contrôle qui n’existe pas ou n’a pas tourné.

## 🗺️ Données et contenu

Les modifications éditoriales se font dans `data/catalog.json` et `data/guides/` via PR. Les guides riches sont publics en lecture sur `/api/guides` ; les contributions passent par Git, jamais par une écriture API anonyme.

- Conserver les avantages **et** les inconvénients.
- Sourcer les affirmations et distinguer France, Europe et extra-européen.
- Une solution open source extra-européenne n’est pas automatiquement une alternative européenne.
- Laisser `verified_at: null` tant que le scénario n’a pas été réellement vérifié et documenté.
- Régénérer l’instantané avec `make snapshot` après une modification du catalogue.
- Vérifier l’import avec `make db-seed` ou le démarrage Docker.
- Ne pas utiliser les logos ou marques citées comme s’ils étaient ceux du projet.

## 🧑‍💻 Conventions de code

- PHP avec `declare(strict_types=1)` et requêtes PDO préparées.
- JavaScript Vue en Composition API, sans introduire TypeScript ou un framework CSS sans discussion.
- Pas de polices, images ou scripts tiers ajoutés inutilement : le projet doit rester sobre et cohérent avec son sujet.
- Pas de nouvelle dépendance sans vérifier qu’elle est nécessaire et compatible avec les versions existantes.
- Pas de back-office ou d’authentification dans la v0.1 : l’API publique reste en lecture seule.
- Dans l’environnement du mainteneur, préfixer les commandes shell avec `rtk` comme demandé par le `AGENTS.md` parent.

## 🏷️ Commits, SemVer et changelog

- Utiliser les Conventional Commits : `feat`, `fix`, `docs`, `ci`, `chore`, etc.
- `.version` est la référence SemVer, synchronisée avec `client/package.json` et `client/package-lock.json`.
- Ne pas éditer `CHANGELOG.md` à la main : utiliser `make changelog`.
- Préparer les notes technique et marketing dans `docs/releases/` avec captures si nécessaire, puis lancer `make release VERSION=x.y.z`, relire le diff, committer, taguer `vx.y.z` et pousser branche et tag.
- Ne pas régénérer le changelog d’une version déjà taguée pour y ajouter des commits ultérieurs : préparer la version suivante.

## 🚀 Production Gandi

Vhost : `/mnt/gandi/vhosts/desarrimage.prigent.tech/htdocs`.

```bash
make deploy       # Front uniquement
make push         # API + données + front
make deploy-dry
make verify
make verify-api
```

Règles impératives :

- Ne jamais committer, afficher, copier ou transmettre les secrets.
- Ne pas lire les fichiers `.env` avec un outil ou une commande shell.
- Ne pas écraser le `.env` de production ; les rsync l’excluent.
- Préserver `api/` et `data/` pendant les déploiements du front.
- Utiliser les variables `DESARRIMAGE_DB_*` pour éviter les collisions avec l’environnement Gandi.
- Exécuter les migrations et le seed de production depuis le serveur Gandi.
- Toute action externe ou difficile à annuler nécessite l’accord du mainteneur.

## 📚 Documentation

Après une modification d’architecture, de commande, de licence, de versionnement ou de déploiement, mettre à jour dans le même changement :

- `README.md` pour les utilisateurs et contributeurs ;
- `api/openapi.yaml` pour toute modification d’endpoint, de paramètre ou de schéma ;
- `CONTRIBUTING.md` pour le processus éditorial ;
- `AGENTS.md` pour les futurs agents ;
- les badges uniquement s’ils représentent un état réel.

Swagger UI est copié par `client/scripts/sync-api-docs.mjs` dans `client/public/swagger-assets/`, répertoire généré et ignoré par Git. L’interface est `/swagger.html` et la spécification `/api/openapi.yaml` ; ne pas utiliser de chemin public commençant par `/api/` pour ces assets, car le proxy Vite les transmettrait à PHP. `client/spec/` est ignoré par Git : Podman peut y créer un fichier vide lors du montage de la spécification dans le conteneur.
