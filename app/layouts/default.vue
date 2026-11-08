<template>
  <div class="layout">
    <a class="skip-link" href="#main">Skip to content</a>
    <SiteHeader />
    <main id="main" class="layout-main">
      <slot />
    </main>
    <SiteFooter />
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.layout-main {
  flex: 1;
}

.skip-link {
  position: absolute;
  top: var(--space-2);
  left: var(--space-2);
  z-index: 10;
  padding: var(--space-2) var(--space-3);
  background: var(--color-secondary);
  color: var(--color-bg);
  border-radius: var(--radius-sm);
  transform: translateY(-200%);
}

.skip-link:focus {
  transform: none;
}
</style>
