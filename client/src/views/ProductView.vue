<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useCatalogStore } from '../stores/catalog'
import { setSeo } from '../services/seo'
import MigrationGuide from '../components/MigrationGuide.vue'
import CountryFlag from '../components/CountryFlag.vue'

const route = useRoute()
const catalog = useCatalogStore()
const product = ref(null)
const loading = ref(true)
const error = ref(null)
const selectedId = ref(null)

const selectedMigration = computed(() => {
  if (!product.value?.migrations?.length) return null
  return product.value.migrations.find((migration) => migration.id === selectedId.value) || product.value.migrations[0]
})

const difficultyLabels = {
  facile: 'Traversée facile',
  intermediaire: 'Quelques manœuvres',
  avancee: 'Navigation expérimentée',
}

function onTabKeydown(event) {
  const keys = ['ArrowLeft', 'ArrowRight', 'Home', 'End']
  if (!keys.includes(event.key) || !product.value?.migrations?.length) return
  event.preventDefault()
  const migrations = product.value.migrations
  const currentIndex = migrations.findIndex((migration) => migration.id === selectedId.value)
  let nextIndex = currentIndex
  if (event.key === 'ArrowLeft') nextIndex = (currentIndex - 1 + migrations.length) % migrations.length
  if (event.key === 'ArrowRight') nextIndex = (currentIndex + 1) % migrations.length
  if (event.key === 'Home') nextIndex = 0
  if (event.key === 'End') nextIndex = migrations.length - 1
  selectedId.value = migrations[nextIndex].id
  nextTick(() => document.getElementById(`tab-${selectedId.value}`)?.focus())
}

watch(
  () => route.params.slug,
  async (slug) => {
    loading.value = true
    error.value = null
    product.value = null
    selectedId.value = null
    try {
      product.value = await catalog.fetchProduct(slug)
      if (product.value.guide_slug) {
        product.value.guide = await catalog.fetchGuide(product.value.guide_slug)
      }
      selectedId.value = product.value.migrations[0]?.id || null
      setSeo({
        title: `${product.value.name} — Désarrimage`,
        description: product.value.description,
        path: route.fullPath,
        image: '/images/social-google-drive.png',
        type: 'article',
        schema: {
          '@context': 'https://schema.org',
          '@type': 'TechArticle',
          headline: product.value.guide?.title || product.value.name,
          description: product.value.description,
          dateModified: product.value.guide?.updated_at,
          inLanguage: 'fr-FR',
          author: { '@type': 'Organization', name: 'Désarrimage' },
        },
      })
      await nextTick()
      if (route.hash) {
        document.querySelector(route.hash)?.scrollIntoView({ block: 'start' })
      }
    } catch (requestError) {
      error.value = requestError.message
    } finally {
      loading.value = false
    }
  },
  { immediate: true },
)
</script>

<template>
  <div v-if="loading" class="page-loading container"><span></span><p>Chargement de la fiche…</p></div>
  <div v-else-if="error" class="page-loading container">
    <h1>Cette fiche reste au large.</h1>
    <p>{{ error }}</p>
    <RouterLink class="button primary" to="/catalogue">Retour au catalogue</RouterLink>
  </div>

  <template v-else-if="product">
    <section class="product-hero page-hero">
      <div class="container">
        <nav class="breadcrumbs" aria-label="Fil d’Ariane">
          <RouterLink to="/catalogue">Catalogue</RouterLink><span>→</span><span>{{ product.category_name }}</span><span>→</span><strong>{{ product.name }}</strong>
        </nav>
        <div class="product-hero-grid">
          <div>
            <div class="product-title-line">
              <span class="product-monogram large" :class="product.region" aria-hidden="true">{{ product.name.charAt(0) }}</span>
              <div><p class="product-category">{{ product.category_name }}</p><h1>{{ product.name }}</h1></div>
            </div>
            <p class="product-lead">{{ product.description }}</p>
            <div class="product-meta">
              <span>{{ product.vendor }}</span><span><CountryFlag :code="product.country_code" :country-name="product.country_name" />{{ product.country_name }}</span><span>{{ product.license_name }}</span>
            </div>
          </div>
          <aside class="product-hero-aside">
            <span :class="['origin-badge', product.region]">{{ product.region === 'other' ? 'Service à remplacer' : product.region === 'fr' ? 'Alternative française' : 'Alternative européenne' }}</span>
            <strong v-if="product.migrations.length">{{ product.migrations.length }}</strong>
            <p v-if="product.migrations.length">destination{{ product.migrations.length > 1 ? 's' : '' }} possible{{ product.migrations.length > 1 ? 's' : '' }}</p>
            <p v-if="product.pricing">{{ product.pricing }}</p>
            <a :href="product.website" target="_blank" rel="noopener">Site officiel ↗</a>
          </aside>
        </div>
      </div>
    </section>

    <section v-if="product.migrations.length" class="section product-detail-section">
      <div class="container">
        <div class="section-heading">
          <p class="eyebrow"><span></span> Vos destinations</p>
          <h2>Quel cap voulez-vous prendre ?</h2>
        </div>
        <div class="destination-tabs" role="tablist" aria-label="Alternatives disponibles" @keydown="onTabKeydown">
          <button
            v-for="migration in product.migrations"
            :id="`tab-${migration.id}`"
            :key="migration.id"
            role="tab"
            :aria-selected="selectedMigration.id === migration.id"
            :aria-controls="`panel-${migration.id}`"
            :tabindex="selectedMigration.id === migration.id ? 0 : -1"
            :class="{ active: selectedMigration.id === migration.id }"
            @click="selectedId = migration.id"
          >
            <span class="product-monogram" :class="migration.target.region" aria-hidden="true">{{ migration.target.name.charAt(0) }}</span>
            <span><strong>{{ migration.target.name }}</strong><small><CountryFlag :code="migration.target.country_code" :country-name="migration.target.country_name" />{{ migration.target.country_name }} · {{ migration.target.vendor }}</small></span>
            <i aria-hidden="true">→</i>
          </button>
        </div>

        <MigrationGuide v-if="product.guide" :guide="product.guide" />

        <article
          v-if="selectedMigration"
          :id="`panel-${selectedMigration.id}`"
          class="migration-panel"
          role="tabpanel"
          :aria-labelledby="`tab-${selectedMigration.id}`"
        >
          <header class="migration-summary">
            <div>
              <p class="eyebrow"><span></span> Plan de traversée</p>
              <h2>{{ product.name }} <em>vers</em> {{ selectedMigration.target.name }}</h2>
              <p>{{ selectedMigration.summary }}</p>
            </div>
            <div class="migration-facts">
              <div><small>Difficulté</small><strong>{{ difficultyLabels[selectedMigration.difficulty] }}</strong></div>
              <div><small>Temps estimé</small><strong>{{ selectedMigration.duration }}</strong></div>
              <div><small>État du guide</small><strong>{{ selectedMigration.verified_at ? `Vérifié le ${selectedMigration.verified_at}` : 'Contenu initial à vérifier' }}</strong></div>
            </div>
          </header>

          <div class="comparison-grid">
            <div class="comparison-card advantages">
              <h3><span>+</span> Ce que vous y gagnez</h3>
              <ul><li v-for="advantage in selectedMigration.advantages" :key="advantage">{{ advantage }}</li></ul>
            </div>
            <div class="comparison-card disadvantages">
              <h3><span>−</span> Ce qu’il faut anticiper</h3>
              <ul><li v-for="disadvantage in selectedMigration.disadvantages" :key="disadvantage">{{ disadvantage }}</li></ul>
            </div>
          </div>

          <div class="migration-guide-grid">
            <div>
              <div class="section-heading compact-heading">
                <p class="eyebrow"><span></span> Mode opératoire</p>
                <h2>La traversée, étape par étape</h2>
              </div>
              <ol class="migration-steps">
                <li v-for="step in selectedMigration.steps" :key="step.position">
                  <span>{{ String(step.position).padStart(2, '0') }}</span>
                  <div><h3>{{ step.title }}</h3><p>{{ step.body }}</p></div>
                </li>
              </ol>
            </div>
            <aside class="migration-sidebar">
              <div class="destination-card">
                <p class="eyebrow"><span></span> À l’arrivée</p>
                <h3>{{ selectedMigration.target.name }}</h3>
                <p>{{ selectedMigration.target.description }}</p>
                <dl>
                  <div><dt>Éditeur</dt><dd>{{ selectedMigration.target.vendor }}</dd></div>
                  <div><dt>Pays</dt><dd>{{ selectedMigration.target.country_name }}</dd></div>
                  <div><dt>Licence</dt><dd>{{ selectedMigration.target.license_name }}</dd></div>
                  <div><dt>Tarifs</dt><dd>{{ selectedMigration.target.pricing }}</dd></div>
                </dl>
                <a class="button primary full-width" :href="selectedMigration.target.website" target="_blank" rel="noopener">Découvrir {{ selectedMigration.target.name }} ↗</a>
              </div>
              <div class="sources-card">
                <h3>Sources officielles</h3>
                <a v-for="source in selectedMigration.sources" :key="source.url" :href="source.url" target="_blank" rel="noopener">{{ source.label }} <span>↗</span></a>
                <p>Guides d’amorçage : vérifiez les options et tarifs actuels avant votre migration. Signalez toute information obsolète.</p>
              </div>
            </aside>
          </div>
        </article>
      </div>
    </section>

    <section v-else class="section product-detail-section">
      <div class="container empty-state">
        <h2>Cette alternative attend son premier guide.</h2>
        <p>Vous l’utilisez pour remplacer un service extra-européen ? Votre retour d’expérience peut aider les prochains.</p>
        <RouterLink class="button primary" to="/contribuer">Documenter une migration</RouterLink>
      </div>
    </section>
  </template>
</template>
