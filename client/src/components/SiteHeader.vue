<script setup>
import { ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const menuOpen = ref(false)
const route = useRoute()
watch(() => route.fullPath, () => { menuOpen.value = false })
</script>

<template>
  <header class="site-header">
    <div class="container header-inner">
      <RouterLink to="/" class="brand" aria-label="Désarrimage, accueil">
        <img src="/images/logo.svg" alt="Désarrimage" width="206" height="46" />
      </RouterLink>
      <button
        class="menu-toggle"
        type="button"
        :aria-expanded="menuOpen"
        aria-controls="navigation-principale"
        aria-label="Ouvrir le menu"
        @click="menuOpen = !menuOpen"
      >
        <span></span><span></span>
      </button>
      <nav id="navigation-principale" :class="['main-nav', { open: menuOpen }]" aria-label="Navigation principale">
        <RouterLink to="/catalogue">Explorer</RouterLink>
        <a href="/swagger.html">API</a>
        <RouterLink to="/#methode">La méthode</RouterLink>
        <RouterLink to="/contribuer">Contribuer</RouterLink>
        <a class="github-link" href="https://github.com/superT0f/desarrimage" target="_blank" rel="noopener">
          GitHub <span aria-hidden="true">↗</span>
        </a>
      </nav>
    </div>
  </header>
</template>
