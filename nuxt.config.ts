// A static site: `nuxt generate` prerenders every page into .output/public, and nginx serves those files
// (tools/nginx/nginx.prod.conf). No server runtime runs in production.
export default defineNuxtConfig({
  compatibilityDate: '2026-11-01',
  devtools: { enabled: false },
  // Project code is never auto-imported, so every import between files is written out and the domain boundaries stay
  // visible (docs/architecture.md, "Auto-imports"). Vue's and Nuxt's own APIs (computed, useHead, NuxtLink) still are.
  components: { dirs: [] },
  imports: { scan: false },
  css: ['@fontsource-variable/inter/wght.css', '~/assets/css/tokens.css', '~/assets/css/base.css'],
  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#000000' },
        { name: 'color-scheme', content: 'dark' },
      ],
      link: [{ rel: 'icon', href: '/favicon.ico', sizes: '16x16' }],
    },
  },
  // Each page's component styles go into its HTML, so it renders without waiting for a stylesheet request
  features: { inlineStyles: true },
  nitro: {
    prerender: {
      routes: ['/', '/privacy', '/sitemap.xml'],
      crawlLinks: true,
      failOnError: true,
      // `privacy.html`, not `privacy/index.html`: nginx serves `/privacy` without a trailing-slash redirect.
      autoSubfolderIndex: false,
    },
  },
  // 404.html is the error page rendered at build time, readable without JavaScript, not an empty app shell.
  experimental: { prerenderErrorPages: true },
  // A component used in a template without an import fails `npm run typecheck`, since none is auto-registered.
  typescript: { strict: true, tsConfig: { vueCompilerOptions: { checkUnknownComponents: true } } },
});
