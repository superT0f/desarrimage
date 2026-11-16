# Contribuer à Désarrimage

Merci de contribuer à des migrations numériques plus simples et documentées.

## Ajouter un produit ou une migration

1. Dupliquez une fiche existante dans `data/catalog.json`.
2. Ajoutez les produits dans `products`, puis la relation dans `migrations`.
3. Renseignez les avantages **et** les inconvénients : une fiche promotionnelle sans compromis n’est pas acceptée.
4. Décrivez des étapes actionnables, dans l’ordre, avec un retour arrière quand c’est possible.
5. Ajoutez des liens officiels et datés dans `sources`.
6. Validez le JSON avec `php -r "json_decode(file_get_contents('data/catalog.json'), true, 512, JSON_THROW_ON_ERROR);"`.
7. Testez l’import avec `php api/scripts/seed.php`, puis ouvrez la fiche dans l’interface.

## Critères éditoriaux

- **France** : éditeur français ou projet principalement développé en France.
- **Europe** : éditeur européen, y compris hors Union européenne ; le pays reste visible.
- Une solution open source extra-européenne n’est pas automatiquement une alternative européenne.
- Les allégations doivent être sourcées, les tarifs datés et les avis distingués des faits.
- Pas de liens d’affiliation sans déclaration explicite.

## Commits et releases

Utilisez les [Conventional Commits](https://www.conventionalcommits.org/fr/v1.0.0/) : `feat: ...`, `fix(api): ...`, `docs: ...`, `ci: ...`. Ils alimentent automatiquement `CHANGELOG.md` avec `make changelog`.

Les versions suivent SemVer. `make release VERSION=x.y.z` synchronise les fichiers de version et régénère le changelog avant la revue et le tag.

## Revue

Ouvrez une pull request en décrivant le scénario testé, sa date et son environnement. Les mainteneurs peuvent demander des sources complémentaires ou une reformulation.
