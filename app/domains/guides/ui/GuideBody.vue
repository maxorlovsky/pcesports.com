<script setup lang="ts">
import type { Block } from '../markdown.ts';
import GuideText from './GuideText.vue';

defineProps<{ blocks: Block[] }>();
</script>

<template>
  <template v-for="(block, index) in blocks" :key="index">
    <h2 v-if="block.kind === 'h2'"><GuideText :inlines="block.inlines" /></h2>
    <h3 v-else-if="block.kind === 'h3'"><GuideText :inlines="block.inlines" /></h3>
    <p v-else-if="block.kind === 'p'"><GuideText :inlines="block.inlines" /></p>
    <ul v-else-if="block.kind === 'ul'">
      <li v-for="(item, itemIndex) in block.items" :key="itemIndex"><GuideText :inlines="item" /></li>
    </ul>
    <ol v-else-if="block.kind === 'ol'">
      <li v-for="(item, itemIndex) in block.items" :key="itemIndex"><GuideText :inlines="item" /></li>
    </ol>
  </template>
</template>
