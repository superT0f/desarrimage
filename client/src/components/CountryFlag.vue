<script setup>
import { computed } from 'vue'

const props = defineProps({
  code: { type: String, required: true },
  countryName: { type: String, required: true },
})

const flags = {
  CH: '🇨🇭',
  DE: '🇩🇪',
  FR: '🇫🇷',
  GB: '🇬🇧',
  US: '🇺🇸',
}
const flag = computed(() => flags[props.code] || '🌍')
</script>

<template>
  <span class="country-flag" role="img" :aria-label="`Pays : ${countryName}`" :title="countryName">{{ flag }}</span>
</template>

<style scoped>
.country-flag { display: inline-block; margin-right: .28em; font-size: 1.1em; line-height: 1; filter: saturate(.85); }
</style>
