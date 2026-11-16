<script setup>
import CountryFlag from './CountryFlag.vue'

defineProps({
  product: { type: Object, required: true },
})
</script>

<template>
  <RouterLink class="product-card" :to="`/produit/${product.slug}`">
    <div class="product-card-top">
      <span class="product-monogram" :class="product.region">{{ product.name.charAt(0) }}</span>
      <span :class="['origin-badge', product.region]">
        {{ product.region === 'other' ? 'À remplacer' : product.country_code }}
      </span>
    </div>
    <div>
      <p class="product-category">{{ product.category_name }}</p>
      <h3>{{ product.name }}</h3>
      <p class="product-vendor">{{ product.vendor }} · <CountryFlag :code="product.country_code" :country-name="product.country_name" />{{ product.country_name }}</p>
    </div>
    <p class="product-description">{{ product.description }}</p>
    <div class="product-card-footer">
      <span v-if="Number(product.alternative_count) > 0">
        {{ product.alternative_count }} migration{{ Number(product.alternative_count) > 1 ? 's' : '' }} documentée{{ Number(product.alternative_count) > 1 ? 's' : '' }}
      </span>
      <span v-else>Voir la fiche</span>
      <span class="round-arrow" aria-hidden="true">→</span>
    </div>
  </RouterLink>
</template>
