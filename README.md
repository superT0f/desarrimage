# Désarrimage

**Changer de cap, pas d’habitudes.**

Désarrimage est un annuaire open source d’alternatives numériques françaises et européennes, avec des modes opératoires concrets pour quitter les services extra-européens : préparation, export, bascule, vérification et retour arrière.

Le projet ne se contente pas de répondre « par quoi remplacer ? », mais aussi « comment migrer, en combien de temps, avec quels compromis ? ».

## Structure

- `api/` — API PHP 8, Slim 4 et MySQL, migrations Phinx.
- `client/` — interface Vue 3, Vite, Pinia, Vue Router et Axios.
- `data/catalog.json` — catalogue contributif et modes opératoires, versionnés dans Git.
- `client/public/images/` — logo et illustrations SVG libres de droits.

## Démarrage local

Prérequis : PHP 8.2+, Composer, MySQL 8+, Node.js 20+.

```bash
make install
# Créer la base MySQL indiquée dans api/.env, puis :
make db-migrate
make db-seed
make dev-api
```

Dans un second terminal :

```bash
make dev-client
```

Le front est disponible sur `http://localhost:5173` et transmet `/api` au serveur PHP local.

## Production Gandi

Le vhost attendu est `/mnt/gandi/vhosts/desarrimage.prigent.tech/htdocs`.

1. Copier `htdocs/api/.env.example` en `htdocs/api/.env` et renseigner les accès MySQL `DESARRIMAGE_DB_*`.
2. Exécuter `make deploy` pour déployer le front seul. La home utilise alors l’instantané du catalogue, sans base.
3. Exécuter `make push` pour déployer aussi l’API et ses dépendances Composer.
4. Sur le serveur Gandi, exécuter `vendor/bin/phinx migrate` puis `php scripts/seed.php` depuis `api/` pour initialiser la base.
5. Vérifier avec `make verify` et `make verify-api`.

Les déploiements préservent toujours le `.env` de production. `make deploy-dry` et `make status` permettent de contrôler les différences avant et après envoi. L’API ne nécessite aucune clé pour la consultation publique.

## Contribuer

Les fiches sont modifiées dans `data/catalog.json` via une pull request. Chaque alternative doit indiquer son pays d’origine, son modèle économique et ses sources. Un mode opératoire doit être testé, daté et préciser les limites ou pertes éventuelles.

Consultez le guide de contribution pour le format attendu.

## Licences

- Code : **AGPL-3.0-or-later**, voir `LICENSE`.
- Contenu du catalogue : **CC BY-SA 4.0**, voir `data/LICENSE`.
- Logo et illustrations : **CC BY-SA 4.0**.

Le code peut être utilisé, modifié et partagé, y compris commercialement. Toute version modifiée distribuée ou proposée comme service réseau doit conserver ces libertés et offrir son code source correspondant sous la même licence. Les contenus adaptés doivent également rester sous CC BY-SA 4.0, avec attribution.

Copyright (c) 2026 Désarrimage contributors.

Désarrimage est un projet indépendant. Les marques citées appartiennent à leurs propriétaires respectifs.
