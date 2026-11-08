<script setup lang="ts">
import type { NuxtError } from '#app';
import ErrorPage from '~/domains/site/views/ErrorPage.vue';

defineProps<{ error: NuxtError }>();
</script>

<template>
  <NuxtLayout>
    <ErrorPage :error="error" />
  </NuxtLayout>
</template>
