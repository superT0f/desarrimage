# Contribuer à Désarrimage

Merci de contribuer à des migrations numériques plus simples et documentées.

Le projet est développé en pair-programming humain–IA par SuperT0f avec **Kimi K3.1 Pro assist**. Les choix restent validés par le mainteneur et les contrôles réellement exécutés sont distingués des simples propositions. Consultez [AGENTS.md](AGENTS.md) pour les conventions techniques et les règles de transparence.

## Ajouter un produit ou une migration

1. Dupliquez une fiche existante dans `data/catalog.json`.
2. Ajoutez les produits dans `products`, puis la relation dans `migrations`.
3. Renseignez les avantages **et** les inconvénients : une fiche promotionnelle sans compromis n’est pas acceptée.
4. Décrivez des étapes actionnables, dans l’ordre, avec un retour arrière quand c’est possible.
5. Ajoutez des liens officiels et datés dans `sources`.
6. Validez le JSON avec `php -r "json_decode(file_get_contents('data/catalog.json'), true, 512, JSON_THROW_ON_ERROR);"`.
7. Testez l’import avec `php api/scripts/seed.php`, puis ouvrez la fiche dans l’interface.

## Ajouter un guide comparatif complet

Les guides riches vivent dans `data/guides/<source>.json`, séparément du catalogue. Ils décrivent un scénario mesurable : utilisateurs, volume, fenêtre de migration, tarif du jour-homme, critères, coûts, risques, chronologie et sources.

Chaque ligne de `comparison` contient un critère et une valeur par produit cible. Modifiez uniquement la cellule concernée dans votre PR. Les coûts utilisent des centimes d’euros et des jours-homme ; l’API calcule les coûts de migration, d’exploitation et de première année. N’inscrivez donc pas de total calculé à la main.

Les estimations doivent indiquer leur date, leurs hypothèses et leurs sources. Elles ne sont jamais présentées comme un devis. `verified_at` reste `null` tant que le scénario n’a pas été réellement testé. `make check` valide la structure et les produits référencés ; l’import Docker peuple ensuite les tables normalisées et expose le guide sur `/api/guides/<slug>`.

## Critères éditoriaux

- **France** : éditeur français ou projet principalement développé en France.
- **Europe** : éditeur européen, y compris hors Union européenne ; le pays reste visible.
- Une solution open source extra-européenne n’est pas automatiquement une alternative européenne.
- Les allégations doivent être sourcées, les tarifs datés et les avis distingués des faits.
- Pas de liens d’affiliation sans déclaration explicite.

## Accessibilité et interface

Toute modification visuelle doit rester lisible dans les thèmes clair et sombre. Vérifiez la navigation clavier, le focus, les libellés et `make audit`. Les trois scores Lighthouse — accessibilité, bonnes pratiques et SEO — doivent rester à 100/100.

## Commits et releases

Utilisez les [Conventional Commits](https://www.conventionalcommits.org/fr/v1.0.0/) : `feat: ...`, `fix(api): ...`, `docs: ...`, `ci: ...`. Ils alimentent automatiquement `CHANGELOG.md` avec `make changelog`.

Les versions suivent SemVer. `make release VERSION=x.y.z` synchronise les fichiers de version et régénère le changelog avant la revue et le tag.

Avant une release marquante, préparez `docs/releases/x.y.z-tech.md` et `docs/releases/x.y.z-marketing.md`, avec des captures utiles dans `docs/images/`. La note technique liste les changements et vérifications ; la note marketing explique les bénéfices sans masquer les limites ni transformer les estimations en promesses.

## Revue

Ouvrez une pull request en décrivant le scénario testé, sa date et son environnement. Les mainteneurs peuvent demander des sources complémentaires ou une reformulation.
