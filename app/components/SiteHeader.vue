<template>
  <header class="site-header">
    <div class="container site-header-inner">
      <NuxtLink to="/" class="brand">
        <img src="/logo.png" alt="" width="57" height="40" class="brand-logo">
        <span class="brand-name">PCesports</span>
      </NuxtLink>
      <nav aria-label="Main">
        <ul class="nav">
          <li><NuxtLink to="/" class="nav-link">What to watch</NuxtLink></li>
        </ul>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  position: sticky;
  top: 0;
  z-index: 5;
  background: rgba(0, 0, 0, 0.82);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--color-border);
}

.site-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  min-height: 4rem;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  color: var(--color-text);
  text-decoration: none;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-lg);
  letter-spacing: -0.01em;
}

.brand-logo {
  display: block;
  width: auto;
  height: 40px;
}

.nav {
  display: flex;
  gap: var(--space-5);
  margin: 0;
  padding: 0;
  list-style: none;
}

.nav-link {
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
}

.nav-link:hover,
.nav-link.router-link-exact-active {
  color: var(--color-text);
}

.nav-link.router-link-exact-active {
  text-decoration: underline;
  text-decoration-color: var(--color-secondary);
  text-decoration-thickness: 2px;
  text-underline-offset: 0.45em;
}

@media (width <= 420px) {
  .brand-name {
    font-size: var(--font-size-md);
  }
}
</style>
