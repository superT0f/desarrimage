<script setup>
import { computed, ref } from 'vue'

const theme = ref(document.documentElement.dataset.theme || 'light')
const isDark = computed(() => theme.value === 'dark')
const label = computed(() => isDark.value ? 'Activer le thème clair' : 'Activer le thème sombre')

function toggleTheme() {
  theme.value = isDark.value ? 'light' : 'dark'
  document.documentElement.dataset.theme = theme.value
  try {
    localStorage.setItem('desarrimage-theme', theme.value)
  } catch {}
}
</script>

<template>
  <button
    class="theme-toggle"
    type="button"
    :aria-label="label"
    :aria-pressed="isDark"
    :title="label"
    @click="toggleTheme"
  >
    <span class="theme-icon" aria-hidden="true">{{ isDark ? '☀' : '☾' }}</span>
    <span class="theme-label">{{ isDark ? 'Clair' : 'Sombre' }}</span>
  </button>
</template>
