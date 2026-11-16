<script setup>
const appVersion = __APP_VERSION__
</script>

<template>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <img src="/images/logo-light.svg" alt="Désarrimage" width="206" height="46" />
        <p>Des alternatives européennes.<br />Des migrations vraiment faisables.</p>
      </div>
      <div class="footer-links">
        <strong>Naviguer</strong>
        <RouterLink to="/catalogue">Le catalogue</RouterLink>
        <RouterLink to="/contribuer">Proposer une fiche</RouterLink>
        <a href="https://github.com/superT0f/desarrimage" target="_blank" rel="noopener">Code source ↗</a>
      </div>
      <div class="footer-note">
        <span class="eyebrow light">Projet indépendant & open source</span>
        <p>Code sous <a href="https://github.com/superT0f/desarrimage/blob/main/LICENSE" target="_blank" rel="noopener">AGPL-3.0-or-later</a>. Contenus et visuels sous <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.fr" target="_blank" rel="noopener">CC BY-SA 4.0</a>. Les marques citées appartiennent à leurs propriétaires.</p>
      </div>
    </div>
    <div class="container footer-bottom">
      <span>Fait en France, avec des liens plutôt que des chaînes.</span>
      <span>v{{ appVersion }} — en construction</span>
    </div>
  </footer>
</template>
