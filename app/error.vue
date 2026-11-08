<script setup lang="ts">
import type { NuxtError } from '#app';

const props = defineProps<{ error: NuxtError }>();
const notFound = computed(() => props.error.statusCode === 404);

usePageSeo({
  title: notFound.value ? 'Page not found' : 'Something went wrong',
  description: notFound.value ? 'This page does not exist on PCesports.' : 'Something went wrong on PCesports.',
  path: '/404',
  noindex: true,
});
</script>

<template>
  <NuxtLayout>
    <div class="container error-page">
      <h1>{{ notFound ? 'This page is not here' : 'Something went wrong' }}</h1>
      <p class="text">
        {{ notFound
          ? 'It may have been part of an older version of PCesports, which is gone now.'
          : 'Try again in a moment.' }}
      </p>
      <NuxtLink to="/" class="home-link">Go to the home page</NuxtLink>
    </div>
  </NuxtLayout>
</template>

<style scoped>
.error-page {
  padding-block: var(--space-8);
  text-align: center;
}

.text {
  color: var(--color-text-muted);
}

.home-link {
  display: inline-block;
  margin-top: var(--space-4);
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-md);
  background: var(--color-main);
  color: var(--color-bg);
  font-weight: var(--font-weight-bold);
  text-decoration: none;
}

.home-link:hover {
  background: var(--color-main-strong);
  color: var(--color-bg);
}
</style>
