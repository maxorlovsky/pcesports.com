<script setup lang="ts">
import { startAnalytics, useAnalyticsConsent } from './useAnalyticsConsent.ts';

const { consent, reopened, bannerOpen, accept, decline } = useAnalyticsConsent();
const nuxtApp = useNuxtApp();
const banner = useTemplateRef<HTMLElement>('banner');

onMounted(() => startAnalytics(nuxtApp));

// Opened again from the footer: move the focus to it, so a keyboard user lands on the choice
watch(reopened, async (open) => {
  if (!open) return;
  await nextTick();
  banner.value?.querySelector('button')?.focus();
});
</script>

<template>
  <section v-if="bannerOpen" ref="banner" class="consent" aria-label="Analytics cookies">
    <div class="container consent-inner">
      <p class="consent-text">
        May we count your visit with Google Analytics? It sets cookies, and loads only if you accept.
        <NuxtLink to="/privacy">What it collects</NuxtLink>
        <template v-if="reopened && consent">
          <br>Your choice now: {{ consent === 'granted' ? 'Accept' : 'Decline' }}.
        </template>
      </p>
      <div class="consent-actions">
        <button type="button" class="consent-button" @click="accept">Accept</button>
        <button type="button" class="consent-button" @click="decline">Decline</button>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* In the page's flow after the footer, held to the bottom of the screen: scrolled to the end, it hides nothing. */
.consent {
  position: sticky;
  bottom: 0;
  z-index: 6;
  border-top: 1px solid var(--color-border-strong);
  background: var(--color-surface-raised);
}

.consent-inner {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3) var(--space-5);
  padding-block: var(--space-4);
}

.consent-text {
  flex: 1 1 24rem;
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  line-height: 1.5;
}

.consent-actions {
  display: flex;
  gap: var(--space-3);
}

/* Both choices look the same: neither is the highlighted default. */
.consent-button {
  min-width: 7rem;
  padding: var(--space-2) var(--space-5);
  border: 1px solid var(--color-main);
  border-radius: var(--radius-md);
  background: transparent;
  color: var(--color-text);
  font: inherit;
  font-weight: var(--font-weight-medium);
  cursor: pointer;
}

.consent-button:hover {
  background: var(--color-main-soft);
}

@media (width <= 420px) {
  .consent-actions {
    flex: 1 1 100%;
  }

  .consent-button {
    flex: 1;
    min-width: 0;
  }
}
</style>
