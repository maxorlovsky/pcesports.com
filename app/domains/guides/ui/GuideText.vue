<script setup lang="ts">
import type { Inline } from '../markdown.ts';

defineProps<{ inlines: Inline[] }>();
</script>

<template>
  <template v-for="(inline, index) in inlines" :key="index">
    <strong v-if="inline.kind === 'strong'">{{ inline.text }}</strong>
    <NuxtLink v-else-if="inline.kind === 'link' && inline.href.startsWith('/')" :to="inline.href">{{ inline.text }}</NuxtLink>
    <a v-else-if="inline.kind === 'link'" :href="inline.href" rel="noopener">{{ inline.text }}</a>
    <template v-else>{{ inline.text }}</template>
  </template>
</template>
