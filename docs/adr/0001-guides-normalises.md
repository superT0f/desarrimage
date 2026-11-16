# ADR 0001 — Guides de migration normalisés et réutilisables

## Contexte

Les annuaires répondent à « par quoi remplacer ? » mais pas à « comment migrer ? ». Une fiche riche mélange scénario, comparaison, coûts, risques, planning et sources. Un champ JSON unique en base rendrait ces données difficiles à relire, interroger et faire évoluer séparément.

## Décision

Chaque guide possède un fichier éditorial dans `data/guides/`, validé par CI, puis importé dans des tables normalisées. Les critères, cellules de comparaison, coûts, risques, phases et sources sont des enregistrements autonomes.

L’API publique est en lecture seule. Les écritures passent par des pull requests, jamais par une modification anonyme. Un miroir statique permet le pré-rendu et la consultation dégradée sans base.

Les coûts calculés sont produits par l’API, pas stockés comme des totaux éditoriaux. Les preuves suivent quatre niveaux : `draft`, `documented`, `tested`, `verified`.

## Conséquences

- Chaque cellule peut être corrigée dans une petite PR.
- Les autres projets peuvent réutiliser les données via JSON, OpenAPI ou miroir statique.
- Les coûts restent recalculables lorsque le taux horaire ou le scénario change.
- La modération reste nécessaire pour éviter les affirmations non vérifiées.
- Le modèle relationnel doit être migré avec Phinx et seedé de façon idempotente.

## Alternatives écartées

- **Un document JSON unique par guide en base** : plus rapide à écrire, mais moins interrogeable et moins contributif.
- **Une API d’écriture publique** : plus dynamique, mais incompatible avec la confiance et la modération recherchées.
- **Des scores propriétaires non publiés** : rejeté car ils empêcheraient la réutilisation et la critique.
