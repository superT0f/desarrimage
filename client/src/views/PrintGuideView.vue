<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import MigrationGuide from '../components/MigrationGuide.vue'
import { useCatalogStore } from '../stores/catalog'
import { setSeo } from '../services/seo'

const route = useRoute()
const catalog = useCatalogStore()
const guide = ref(null)
const error = ref(null)

onMounted(async () => {
  document.body.classList.add('print-mode')
  try {
    const product = await catalog.fetchProduct(route.params.slug)
    if (!product.guide_slug) throw new Error('Ce produit ne possède pas encore de guide complet.')
    guide.value = await catalog.fetchGuide(product.guide_slug)
    setSeo({
      title: `${guide.value.source.name} : guide à imprimer — Désarrimage`,
      description: guide.value.summary,
      path: route.fullPath,
      image: '/images/social-google-drive.png',
      type: 'article',
    })
  } catch (requestError) {
    error.value = requestError.message
  }
})
onUnmounted(() => document.body.classList.remove('print-mode'))
</script>

<template>
  <div class="container print-container">
    <div class="print-actions">
      <RouterLink :to="`/produit/${route.params.slug}`">← Retour au guide</RouterLink>
      <button class="button primary" type="button" @click="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>
    <p v-if="error" class="notice error-notice" role="alert">{{ error }}</p>
    <p v-else-if="!guide" class="page-loading">Préparation du document A4…</p>
    <MigrationGuide v-else :guide="guide" />
  </div>
</template>
