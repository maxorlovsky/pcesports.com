<script setup lang="ts">
import { DATA_CREDITS } from '../site.ts';
import { useAnalyticsConsent } from './useAnalyticsConsent.ts';

const { ready, reopen } = useAnalyticsConsent();

// The year of the build; every deploy rebuilds the site
const year = new Date().getFullYear();
</script>

<template>
  <footer class="site-footer">
    <div class="container site-footer-inner">
      <p class="copyright">© 2014-{{ year }} PCesports.com</p>
      <ul v-if="DATA_CREDITS.length" class="credits" aria-label="Data credits">
        <li v-for="credit in DATA_CREDITS" :key="credit.url">
          <a :href="credit.url" rel="noopener">{{ credit.label }}</a>
        </li>
      </ul>
      <nav aria-label="Footer">
        <ul class="footer-links">
          <li><NuxtLink to="/privacy">Privacy</NuxtLink></li>
          <!-- Only once the page runs its script: without one there is nothing to choose -->
          <li v-if="ready"><button type="button" class="footer-button" @click="reopen">Cookie settings</button></li>
        </ul>
      </nav>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  margin-top: var(--space-8);
  border-top: 1px solid var(--color-border);
  background: var(--color-surface);
  color: var(--color-text-faint);
  font-size: var(--font-size-sm);
}

.site-footer-inner {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3) var(--space-6);
  padding-block: var(--space-6);
}

.copyright {
  margin: 0;
}

.credits,
.footer-links {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
  margin: 0;
  padding: 0;
  list-style: none;
}

.site-footer a {
  color: var(--color-text-muted);
}

.site-footer a:hover {
  color: var(--color-text);
}

/* A button that reads as the links beside it */
.footer-button {
  padding: 0;
  border: 0;
  background: none;
  color: var(--color-text-muted);
  font: inherit;
  text-decoration: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 0.2em;
  cursor: pointer;
}

.footer-button:hover {
  color: var(--color-text);
}
</style>
