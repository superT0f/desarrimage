<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductCard from '../components/ProductCard.vue'
import { useCatalogStore } from '../stores/catalog'

const catalog = useCatalogStore()
const route = useRoute()
const router = useRouter()
const search = ref('')

onMounted(() => catalog.loadCategories().catch(() => {}))
watch(
  () => route.query,
  (query) => {
    search.value = query.search || ''
    catalog.fetchProducts({
      search: query.search || undefined,
      category: query.category || undefined,
      region: query.region || 'other',
    })
  },
  { immediate: true },
)

function updateQuery(values) {
  const query = { ...route.query, ...values }
  Object.keys(query).forEach((key) => {
    if (!query[key] || (key === 'region' && query[key] === 'other')) delete query[key]
  })
  router.push({ name: 'catalog', query })
}

function reset() {
  search.value = ''
  router.push({ name: 'catalog' })
}
</script>

<template>
  <section class="catalog-hero page-hero">
    <div class="container">
      <p class="eyebrow light"><span></span> Le catalogue</p>
      <h1>Quel outil voulez-vous<br /><em>laisser au quai ?</em></h1>
      <p>Choisissez un service pour découvrir ses alternatives et le chemin pour les rejoindre.</p>
    </div>
  </section>

  <section class="section catalog-section">
    <div class="container">
      <form class="catalog-search" role="search" @submit.prevent="updateQuery({ search })">
        <label class="sr-only" for="catalog-search">Rechercher un produit</label>
        <span aria-hidden="true">⌕</span>
        <input id="catalog-search" v-model="search" type="search" placeholder="Rechercher Gmail, Slack, Nextcloud…" />
        <button class="button primary" type="submit">Rechercher</button>
      </form>

      <div class="catalog-toolbar">
        <div class="region-filters" aria-label="Filtrer par origine">
          <button :class="{ active: !route.query.region || route.query.region === 'other' }" @click="updateQuery({ region: 'other' })">À remplacer</button>
          <button :class="{ active: route.query.region === 'fr' }" @click="updateQuery({ region: 'fr' })">France</button>
          <button :class="{ active: route.query.region === 'europe' }" @click="updateQuery({ region: 'europe' })">Europe</button>
          <button :class="{ active: route.query.region === 'all' }" @click="updateQuery({ region: 'all' })">Tous</button>
        </div>
        <label class="category-select">
          <span class="sr-only">Catégorie</span>
          <select :value="route.query.category || ''" @change="updateQuery({ category: $event.target.value })">
            <option value="">Toutes les catégories</option>
            <option v-for="category in catalog.categories" :key="category.id" :value="category.slug">{{ category.name }}</option>
          </select>
        </label>
      </div>

      <div class="catalog-result-meta">
        <p><strong>{{ catalog.products.length }}</strong> résultat{{ catalog.products.length > 1 ? 's' : '' }}</p>
        <button v-if="Object.keys(route.query).length" class="clear-filter" @click="reset">Effacer les filtres ×</button>
      </div>

      <div v-if="catalog.error" class="notice error-notice">
        <strong>Le catalogue n’a pas répondu.</strong> {{ catalog.error }}
      </div>
      <div v-else-if="catalog.loading" class="loading-grid"><span></span><span></span><span></span></div>
      <div v-else-if="catalog.products.length" class="product-grid">
        <ProductCard v-for="product in catalog.products" :key="product.id" :product="product" />
      </div>
      <div v-else class="empty-state">
        <span aria-hidden="true">⌁</span>
        <h2>Aucune traversée trouvée</h2>
        <p>Essayez un autre nom ou élargissez les filtres.</p>
        <button class="button primary" @click="reset">Voir les outils à remplacer</button>
      </div>
    </div>
  </section>
</template>
