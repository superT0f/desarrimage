# Gouvernance de Désarrimage

Désarrimage est un commun numérique ouvert. Le projet privilégie les décisions lisibles, les contributions petites et les preuves avant la promotion.

## Rôles

- **Mainteneurs** : responsables de la vision, des releases, de la sécurité et de la décision finale en cas de désaccord durable.
- **Relecteurs** : vérifient les sources, les calculs, les retours d’expérience et la qualité éditoriale. Ils ne peuvent pas valider leur propre témoignage sans contre-revue.
- **Contributeurs** : proposent produits, cellules, guides, code, corrections ou retours d’expérience.
- **Fournisseurs** : peuvent corriger leurs fiches et répondre aux critiques, en déclarant leur affiliation. Aucun droit de retrait ou de mise en avant n’est attaché à ce rôle.

## Niveaux de preuve

- `draft` : proposition non relue, visible seulement si le statut éditorial `draft` est accepté pour publication anticipée.
- `documented` : sources officielles récentes, hypothèses explicites, mais aucune migration réelle revendiquée.
- `tested` : migration effectuée par le déclarant avec environnement, date, volume, difficultés et preuves.
- `verified` : contre-revue indépendante du scénario testé, par une autre organisation ou un relecteur sans lien avec le déclarant.

Une fiche peut être publiée sans être testée. Elle ne doit alors jamais employer « vérifié sur le terrain ».

## Décisions

Les propositions sont discutées publiquement dans les issues et pull requests. Nous recherchons d’abord un consensus mou. En cas de blocage, les mainteneurs tranchent en expliquant la décision dans une ADR.

Les changements de licence, de gouvernance, de modèle de données public, de financement ou de partenariat font obligatoirement l’objet d’une ADR dans `docs/adr/`.

## Indépendance et conflits d’intérêts

- Aucun classement sponsorisé, aucune position achetée, aucun lien d’affiliation.
- Les liens avec un fournisseur doivent être déclarés dans la contribution.
- Les dons ou financements ne donnent aucun droit éditorial particulier.
- Les prestataires ne sont pas recommandés sur la base d’un paiement.
- Les contributions IA sont déclarées et relues comme les autres. Elles ne constituent jamais une preuve de migration.

## Données et fraîcheur

- Les prix doivent indiquer devise, période, date de vérification et source.
- Une revue est demandée au moins tous les 90 jours pour les données tarifaires.
- Les informations obsolètes sont datées et archivées plutôt que supprimées silencieusement.
- Les données sont exportables sous CC BY-SA 4.0, avec leur historique Git.

## Protection des personnes

Aucune donnée personnelle ou secrète dans les preuves : anonymisez noms, domaines, adresses, liens signés et contenus sensibles. Les rapports de sécurité ne passent pas par une issue publique tant qu’ils ne sont pas corrigés.
