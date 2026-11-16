<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'
import { useCatalogStore } from '../stores/catalog'

const catalog = useCatalogStore()
onMounted(catalog.loadHome)
const featured = computed(() => catalog.products.filter((product) => Number(product.alternative_count) > 0).slice(0, 3))
</script>

<template>
  <aside class="release-announcement" aria-label="Annonce de la version 0.4.2">
    <div class="container release-announcement-inner">
      <span class="release-badge">Nouveau · 0.4.2</span>
      <p><strong>La réponse à la grande question.</strong> Google Drive → 3 alternatives : coûts, risques et API publique.</p>
      <div class="release-links">
        <RouterLink to="/produit/google-drive">Découvrir le guide <span>→</span></RouterLink>
        <a href="https://github.com/superT0f/desarrimage/blob/main/docs/releases/0.4.2-marketing.md" target="_blank" rel="noopener">Notes de release ↗</a>
      </div>
    </div>
  </aside>
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-copy">
        <p class="eyebrow"><span></span> Le numérique reprend le large</p>
        <h1>Changez de cap,<br /><em>pas d’habitudes.</em></h1>
        <p class="hero-intro">
          Des alternatives françaises et européennes aux outils du quotidien.
          Surtout, des guides concrets pour migrer sans perdre vos données ni votre équipe en route.
        </p>
        <div class="hero-actions">
          <RouterLink class="button primary" to="/catalogue">Trouver une alternative <span>→</span></RouterLink>
          <a class="text-link" href="#methode">Découvrir la méthode <span>↓</span></a>
        </div>
        <div class="hero-proof">
          <div class="avatar-stack" aria-hidden="true"><span>FR</span><span>UE</span><span>↗</span></div>
          <p><strong>Un projet ouvert et indépendant.</strong><br />Sans publicité. Sans classement sponsorisé.</p>
        </div>
        <ul class="hero-values" aria-label="Engagements du projet">
          <li>AGPL-3.0</li>
          <li>CC BY-SA 4.0</li>
          <li>API ouverte</li>
          <li title="Audit Lighthouse 13.6.0 sur les thèmes clair et sombre">Lighthouse 100/100</li>
          <li title="Validateur HTML du W3C">HTML vérifié W3C</li>
        </ul>
      </div>
      <div class="hero-visual">
        <img src="/images/migration-harbour.svg" alt="Un plan de migration guide des conteneurs de données d’un service extra-européen vers des alternatives européennes." width="680" height="620" />
        <div class="hero-stamp" aria-hidden="true"><span>CAP SUR</span><strong>L’EUROPE</strong><small>ET AU-DELÀ</small></div>
      </div>
    </div>
    <div class="container hero-stats" aria-label="Le catalogue en chiffres">
      <div><strong>{{ catalog.stats?.source_products ?? '—' }}</strong><span>outils à quitter</span></div>
      <div><strong>{{ catalog.stats?.alternatives ?? '—' }}</strong><span>alternatives recensées</span></div>
      <div><strong>{{ catalog.stats?.migrations ?? '—' }}</strong><span>guides de migration</span></div>
      <p>La souveraineté n’est pas un saut dans le vide.<br /><strong>C’est une traversée bien préparée.</strong></p>
    </div>
  </section>

  <section id="methode" class="section method-section">
    <div class="container">
      <div class="section-heading split-heading">
        <div>
          <p class="eyebrow"><span></span> Notre cap</p>
          <h2>Une alternative,<br />c’est bien. <em>La rejoindre,</em><br />c’est mieux.</h2>
        </div>
        <p>Les annuaires donnent une destination. Désarrimage trace la route, signale les courants contraires et vous aide à garder vos données à bord.</p>
      </div>
      <div class="method-grid">
        <article class="method-card">
          <div class="method-number">01</div>
          <div class="method-icon compass-icon" aria-hidden="true">↗</div>
          <h3>Choisissez votre destination</h3>
          <p>Des solutions françaises et européennes, leur pays d’origine, leur licence et leur modèle économique exposés clairement.</p>
          <span class="method-caption">Comparer sans angle mort</span>
        </article>
        <article class="method-card featured-method">
          <div class="method-number">02</div>
          <div class="method-icon route-icon" aria-hidden="true">⌁</div>
          <h3>Suivez la carte au trésor</h3>
          <p>Un mode opératoire ordonné : inventaire, export, test, bascule et retour arrière. Avec une estimation du temps et de la difficulté.</p>
          <span class="method-caption">Migrer sans improviser</span>
        </article>
        <article class="method-card">
          <div class="method-number">03</div>
          <div class="method-icon anchor-icon" aria-hidden="true">⚓</div>
          <h3>Sachez ce qui reste au port</h3>
          <p>Avantages et inconvénients, fonctionnalités perdues, coûts cachés : chaque compromis est écrit noir sur blanc.</p>
          <span class="method-caption">Décider en confiance</span>
        </article>
      </div>
    </div>
  </section>

  <section class="section guide-preview-section">
    <div class="container guide-preview-grid">
      <div class="guide-preview-copy">
        <p class="eyebrow light"><span></span> Le mode opératoire</p>
        <h2>Pas de formule magique.<br /><em>Des étapes qui marchent.</em></h2>
        <p>Chaque guide répond aux questions qu’on se pose vraiment avant de quitter un service.</p>
        <ul class="check-list light-list">
          <li>Quelles données exporter, et dans quel format ?</li>
          <li>Que faut-il tester avant de basculer toute l’équipe ?</li>
          <li>Quelles intégrations ou habitudes vont disparaître ?</li>
          <li>Comment revenir en arrière si le test échoue ?</li>
        </ul>
        <RouterLink class="button coral" to="/produit/google-drive">Voir un guide complet <span>→</span></RouterLink>
      </div>
      <div class="route-sheet" aria-label="Exemple d’étapes de migration">
        <div class="sheet-header"><span>PLAN DE TRAVERSÉE</span><span>v0.1</span></div>
        <div class="sheet-route"><span>Google Drive</span><b>→</b><span>Nextcloud</span></div>
        <ol>
          <li><span>1</span><div><strong>Inventorier</strong><small>Données, droits, liens publics</small></div><i>30 min</i></li>
          <li><span>2</span><div><strong>Préparer</strong><small>Destination et sauvegardes</small></div><i>1 h</i></li>
          <li><span>3</span><div><strong>Tester</strong><small>Un échantillon représentatif</small></div><i>2 h</i></li>
          <li><span>4</span><div><strong>Basculer</strong><small>Par lots, retour arrière possible</small></div><i>Variable</i></li>
        </ol>
        <div class="sheet-footer"><span class="status-dot"></span> Préparé étape par étape <span>⚑</span></div>
      </div>
    </div>
  </section>

  <section class="section featured-section">
    <div class="container">
      <div class="section-heading row-heading">
        <div>
          <p class="eyebrow"><span></span> Premières traversées</p>
          <h2>Par où commencer ?</h2>
        </div>
        <RouterLink class="text-link" to="/catalogue">Explorer tout le catalogue <span>→</span></RouterLink>
      </div>
      <div v-if="catalog.error" class="notice error-notice" role="alert">
        {{ catalog.error }} Lancez l’API locale pour charger le catalogue.
      </div>
      <div v-else-if="catalog.loading" class="loading-grid"><span></span><span></span><span></span></div>
      <div v-else class="product-grid">
        <ProductCard v-for="product in featured" :key="product.id" :product="product" />
      </div>
    </div>
  </section>

  <section class="section contribution-banner-section">
    <div class="container contribution-banner">
      <div>
        <p class="eyebrow light"><span></span> Un bien commun</p>
        <h2>Vous avez changé de cap ?<br /><em>Racontez la traversée.</em></h2>
      </div>
      <div>
        <p>Le catalogue vit grâce aux retours d’expérience. Ajoutez une alternative, corrigez une information ou partagez votre propre mode opératoire.</p>
        <RouterLink class="button outline-light" to="/contribuer">Contribuer au projet <span>→</span></RouterLink>
      </div>
    </div>
  </section>
</template>
