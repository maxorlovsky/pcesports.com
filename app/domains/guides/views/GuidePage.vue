<script setup lang="ts">
import { usePageSeo } from '../../site/ui/index.ts';
import type { Guide } from '../guides.ts';
import GuideBody from '../ui/GuideBody.vue';

const slug = String(useRoute().params.slug ?? '');

// Read at build time, on the server: the page's HTML and payload get this guide and nothing else, and the guide files
// never reach the browser.
const { data: guide } = await useAsyncData(`guide-${slug}`, async (): Promise<Guide | null> => {
  if (!import.meta.server) return null;
  const { findGuide } = await import('../load.ts');
  return findGuide(slug);
});

if (!guide.value) throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true });

usePageSeo({ title: guide.value.title, description: guide.value.description, path: `/guides/${slug}` });
</script>

<template>
  <div v-if="guide" class="container page">
    <article class="prose">
      <p class="crumbs"><NuxtLink to="/guides">Guides</NuxtLink></p>
      <h1>{{ guide.title }}</h1>
      <p class="meta">{{ guide.minutes }} min read</p>
      <GuideBody :blocks="guide.blocks" />
      <p class="more"><NuxtLink to="/guides">More guides</NuxtLink> or <NuxtLink to="/">what to watch this week</NuxtLink></p>
    </article>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-7);
}

.crumbs,
.meta {
  color: var(--color-text-faint);
  font-size: var(--font-size-sm);
}

.crumbs {
  margin-bottom: var(--space-3);
}

.meta {
  margin-bottom: var(--space-6);
}

.more {
  margin-top: var(--space-7);
  padding-top: var(--space-5);
  border-top: 1px solid var(--color-border);
  color: var(--color-text-muted);
}
</style>
