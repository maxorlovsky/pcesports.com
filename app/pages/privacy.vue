<script setup lang="ts">
usePageSeo({
  title: 'Privacy',
  description: 'What PCesports collects about you: no accounts, no cookies, no analytics and no access logs.',
  path: '/privacy',
});
</script>

<template>
  <div class="container page">
    <article class="prose">
      <h1>Privacy</h1>
      <p class="updated">Last updated: 8 November 2026</p>

      <p>
        PCesports.com is a small hobby site. This page says what happens to your data when you visit it. In short:
        nothing is collected about you.
      </p>

      <h2>What we collect</h2>
      <ul>
        <li>No accounts, no sign-up and no forms.</li>
        <li>No cookies and no local storage.</li>
        <li>No analytics, no tracking and no ads.</li>
        <li>
          Our server keeps no access logs, so your visits are not recorded with your address or browser. Like any web
          server, its error log can note an address when a request fails.
        </li>
        <li>Fonts and images come from this site itself, not from another company's servers.</li>
      </ul>

      <h2>Cloudflare</h2>
      <p>
        The site is delivered through Cloudflare, which carries every request to our server and protects it from
        attacks. Cloudflare processes technical data such as your IP address to do that, under its own
        <a href="https://www.cloudflare.com/privacypolicy/" rel="noopener">privacy policy</a>.
      </p>

      <h2>Links to other sites</h2>
      <p>
        Links take you to other sites, such as YouTube for match videos. Once you follow one, that site's own privacy
        policy applies.
      </p>

      <h2>Changes</h2>
      <p>
        If PCesports ever adds ads or analytics, this page will say so, and what they collect, before they go live.
      </p>

      <h2>Contact</h2>
      <p>
        Questions about your data: <a :href="`mailto:${CONTACT_EMAIL}`">{{ CONTACT_EMAIL }}</a>.
      </p>
    </article>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-7);
}

.updated {
  color: var(--color-text-faint);
  font-size: var(--font-size-sm);
}
</style>
