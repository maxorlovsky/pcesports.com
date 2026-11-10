<script setup lang="ts">
import { usePageSeo } from '../../site/ui/index.ts';
import type { GuideSummary } from '../guides.ts';

usePageSeo({
  title: 'Guides',
  description: 'Short CS2 guides for busy viewers: watching without spoilers, how the Majors and formats work, and how PCesports rates matches.',
  path: '/guides',
});

// Read at build time, on the server, as each guide's page is: the list's payload holds the summaries only.
const { data: guides } = await useAsyncData('guides', async (): Promise<GuideSummary[]> => {
  if (!import.meta.server) return [];
  const { guideSummaries } = await import('../load.ts');
  return guideSummaries();
}, { default: () => [] });
</script>

<template>
  <div class="container page">
    <h1>Guides</h1>
    <p class="lead">Short answers for people who don't follow every match. Each one reads in a few minutes.</p>
    <ul class="guides">
      <li v-for="guide in guides" :key="guide.slug" class="guide">
        <h2><NuxtLink :to="`/guides/${guide.slug}`">{{ guide.title }}</NuxtLink></h2>
        <p class="guide-description">{{ guide.description }}</p>
        <p class="guide-meta">{{ guide.minutes }} min read</p>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-7);
}

.lead {
  max-width: var(--width-text);
  color: var(--color-text-muted);
  font-size: var(--font-size-lg);
}

.guides {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
  gap: var(--space-4);
  margin: var(--space-6) 0 0;
  padding: 0;
  list-style: none;
}

.guide {
  display: flex;
  flex-direction: column;
  padding: var(--space-5);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  background: var(--color-surface);
}

.guide h2 {
  margin-bottom: var(--space-3);
  font-size: var(--font-size-lg);
}

.guide h2 a {
  color: var(--color-text);
  text-decoration: none;
}

.guide h2 a:hover {
  color: var(--color-main-strong);
  text-decoration: underline;
}

.guide-description {
  flex: 1;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

.guide-meta {
  margin: 0;
  color: var(--color-text-faint);
  font-size: var(--font-size-xs);
}
</style>
